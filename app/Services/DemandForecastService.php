<?php

namespace App\Services;

use App\Models\Product;
use App\Models\DemandRecord;
use App\Models\ForecastSetting;
use Illuminate\Support\Carbon;

class DemandForecastService
{
    public function resolveWindow(Product $product): int
    {
        $productSetting = ForecastSetting::where('scope', 'product')
            ->where('product_id', $product->id)
            ->first();

        if ($productSetting) {
            return $productSetting->sma_window_days;
        }

        $categorySetting = ForecastSetting::where('scope', 'category')
            ->where('category_id', $product->category_id)
            ->first();

        if ($categorySetting) {
            return $categorySetting->sma_window_days;
        }

        $globalSetting = ForecastSetting::where('scope', 'global')->first();

        // <--- FIXED: Added nullsafe operator to prevent "Attempt to read property on null" errors
        return $globalSetting?->sma_window_days ?? 30;
    }

    private function globalWindowDays(): int
    {
        return ForecastSetting::where('scope', 'global')->first()?->sma_window_days ?? 30;
    }

    public function averageDailyDemand(Product $product): float
    {
        $windowDays = $this->resolveWindow($product);

        $totalDemand = DemandRecord::where('product_id', $product->id)
            ->whereIn('source', ['sale', 'imported'])
            ->where('record_date', '>=', Carbon::today()->subDays($windowDays))
            ->sum('quantity');

        return $windowDays > 0 ? round($totalDemand / $windowDays, 2) : 0;
    }

    public function totalAverageDailyDemand(): float
    {
        $windowDays = $this->globalWindowDays();

        $total = DemandRecord::whereIn('source', ['sale', 'imported'])
            ->where('record_date', '>=', Carbon::today()->subDays($windowDays))
            ->sum('quantity');

        return $windowDays > 0 ? round($total / $windowDays, 2) : 0;
    }

    public function projectedAnnualDemand(Product $product): float
    {
        return round($this->averageDailyDemand($product) * 365, 2);
    }

    public function historicalDailySeries(?int $productId, Carbon $from, Carbon $to): array
    {
        $query = DemandRecord::whereBetween('record_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('source', ['sale', 'imported'])
            ->groupBy('record_date')
            ->selectRaw('record_date, SUM(quantity) as total');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->pluck('total', 'record_date')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    public function chartData(?int $productId, string $range): array
    {
        $config = match ($range) {
            '7days'   => ['lookback' => 7,   'horizon' => 7,   'bucket' => 'day'],
            '14days'  => ['lookback' => 14,  'horizon' => 14,  'bucket' => 'day'],
            '1month'  => ['lookback' => 30,  'horizon' => 30,  'bucket' => 'week'],
            '6months' => ['lookback' => 180, 'horizon' => 180, 'bucket' => 'month'],
            '1yr'     => ['lookback' => 365, 'horizon' => 365, 'bucket' => 'month'],
            default   => ['lookback' => 90,  'horizon' => 90,  'bucket' => 'week'],
        };

        $lookbackStart = now()->subDays($config['lookback'])->startOfDay();
        $today         = now()->startOfDay();
        $horizonEnd    = now()->addDays($config['horizon'])->endOfDay();

        $dailyData = $this->historicalDailySeries($productId, $lookbackStart, $today);

        $avgDaily = $productId
            ? $this->averageDailyDemand(Product::findOrFail($productId))
            : $this->totalAverageDailyDemand();

        $values = array_values($dailyData);
        $stddev = 0.0;
        if (count($values) > 1) {
            $mean = array_sum($values) / count($values);
            $variance = 0.0;
            foreach ($values as $v) {
                $variance += pow($v - $mean, 2);
            }
            $stddev = sqrt($variance / count($values));
        } elseif ($avgDaily > 0) {
            $stddev = $avgDaily * 0.3;
        }

        $labels = $historical = $forecast = $lowerBound = $upperBound = [];
        $cursor       = $lookbackStart->copy();
        $forecastStep = 0;
        $lastHistory  = null;
        $i            = 0;

        while ($cursor <= $horizonEnd) {
            if ($config['bucket'] === 'day') {
                $bucketEnd  = $cursor->copy();
                $label      = $cursor->format('M d');
                $nextCursor = $cursor->copy()->addDay();
                $stepDays   = 1;
            } elseif ($config['bucket'] === 'week') {
                $bucketEnd = $cursor->copy()->addDays(6);
                if ($bucketEnd > $horizonEnd) $bucketEnd = $horizonEnd->copy();
                $label      = $cursor->format('M d');
                $nextCursor = $cursor->copy()->addDays(7);
                $stepDays   = 7;
            } else {
                $bucketEnd = $cursor->copy()->endOfMonth();
                if ($bucketEnd > $horizonEnd) $bucketEnd = $horizonEnd->copy();
                $label      = $cursor->format('M Y');
                $nextCursor = $cursor->copy()->addMonthNoOverflow()->startOfMonth();
                $stepDays   = (int) $cursor->diffInDays($bucketEnd) + 1;
            }

            $labels[] = $label;

            if ($cursor <= $today) {
                $sum = 0.0;
                $d = $cursor->copy();
                while ($d <= $bucketEnd && $d <= $today) {
                    $sum += $dailyData[$d->toDateString()] ?? 0;
                    $d->addDay();
                }
                $historical[]  = $sum;
                $forecast[]    = null;
                $lowerBound[]  = null;
                $upperBound[]  = null;
                $lastHistory   = $i;
            } else {
                $forecastStep++;
                $expected  = $avgDaily * $stepDays;
                $margin    = 1.96 * $stddev * sqrt($stepDays * $forecastStep);
                $historical[]  = null;
                $forecast[]    = (int) round($expected);
                $lowerBound[]  = (int) max(0, round($expected - $margin));
                $upperBound[]  = (int) round($expected + $margin);
            }

            $cursor = $nextCursor;
            $i++;
        }

        if ($lastHistory !== null) {
            $forecast[$lastHistory]   = $historical[$lastHistory];
            $lowerBound[$lastHistory] = $historical[$lastHistory];
            $upperBound[$lastHistory] = $historical[$lastHistory];
        }

        return [
            'labels'       => $labels,
            'historical'   => $historical,
            'forecast'     => $forecast,
            'lower_bound'  => $lowerBound,
            'upper_bound'  => $upperBound,
            'today_index'  => $lastHistory !== null ? $lastHistory + 0.5 : null,
        ];
    }
}