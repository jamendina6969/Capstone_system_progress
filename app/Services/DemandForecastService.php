<?php

namespace App\Services;

use App\Models\Product;
use App\Models\DemandRecord;
use App\Models\SalesRecord;
use App\Models\ForecastSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        return $globalSetting?->sma_window_days ?? 30;
    }

    private function globalWindowDays(): int
    {
        return ForecastSetting::where('scope', 'global')->first()?->sma_window_days ?? 30;
    }

    public function averageDailyDemand(Product $product, ?Carbon $asOfDate = null): float
    {
        $windowDays = $this->resolveWindow($product);
        $endDate = $asOfDate ? $asOfDate->copy() : Carbon::today();
        $startDate = $endDate->copy()->subDays($windowDays);

        $fromDateStr = $startDate->toDateString();
        $toDateStr = $endDate->toDateString();

        $demandSum = (float) DemandRecord::where('product_id', $product->id)
            ->whereIn('source', ['sale', 'imported'])
            ->whereBetween('record_date', [$fromDateStr, $toDateStr])
            ->sum('quantity');

        $posSum = (float) DB::table('transaction_items')
            ->where('product_id', $product->id)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->sum('quantity');

        $totalDemand = $demandSum + $posSum;

        return $windowDays > 0 ? round($totalDemand / $windowDays, 2) : 0;
    }

    public function totalAverageDailyDemand(?Carbon $asOfDate = null): float
    {
        $windowDays = $this->globalWindowDays();
        $endDate = $asOfDate ? $asOfDate->copy() : Carbon::today();
        $startDate = $endDate->copy()->subDays($windowDays);

        $fromDateStr = $startDate->toDateString();
        $toDateStr = $endDate->toDateString();

        $demandSum = (float) DemandRecord::whereIn('source', ['sale', 'imported'])
            ->whereBetween('record_date', [$fromDateStr, $toDateStr])
            ->sum('quantity');

        $posSum = (float) DB::table('transaction_items')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->sum('quantity');

        $total = $demandSum + $posSum;

        return $windowDays > 0 ? round($total / $windowDays, 2) : 0;
    }

    public function projectedAnnualDemand(Product $product): float
    {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lookbackStart = $currentMonthStart->copy()->subMonths(3)->startOfMonth();

        $dailyData = $this->historicalDailySeries($product->id, $lookbackStart, $currentMonthStart->copy()->subDay());

        $monthlyTotals = [];
        $tempCursor = $lookbackStart->copy();
        while ($tempCursor < $currentMonthStart) {
            $mStart = $tempCursor->copy()->startOfMonth();
            $mEnd = $tempCursor->copy()->endOfMonth();

            $monthSum = 0.0;
            $d = $mStart->copy();
            while ($d <= $mEnd) {
                $monthSum += (float) ($dailyData[$d->toDateString()] ?? 0);
                $d->addDay();
            }
            $monthlyTotals[] = $monthSum;
            $tempCursor->addMonthNoOverflow()->startOfMonth();
        }

        // Exact 3 completed past months average * 12
        $avgMonthly = count($monthlyTotals) > 0 ? (array_sum($monthlyTotals) / count($monthlyTotals)) : 0;

        if ($avgMonthly <= 0) {
            return round($this->averageDailyDemand($product) * 365, 2);
        }

        return round($avgMonthly * 12, 2);
    }

    public function historicalDailySeries(?int $productId, Carbon $from, Carbon $to): array
    {
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $demandQuery = DemandRecord::whereBetween('record_date', [$fromDate, $toDate])
            ->whereIn('source', ['sale', 'imported'])
            ->groupBy('record_date')
            ->selectRaw('record_date, SUM(quantity) as total');

        if ($productId) {
            $demandQuery->where('product_id', $productId);
        }
        $demandData = $demandQuery->pluck('total', 'record_date')->toArray();

        $posQuery = DB::table('transaction_items')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as record_date, SUM(quantity) as total')
            ->groupBy('record_date');

        if ($productId) {
            $posQuery->where('product_id', $productId);
        }
        $posData = $posQuery->pluck('total', 'record_date')->toArray();

        // Build continuous daily series with zero-filling for every single day in the range
        $combined = [];
        $cursor = $from->copy();
        while ($cursor <= $to) {
            $dateStr = $cursor->toDateString();
            $dSum = (float) ($demandData[$dateStr] ?? 0);
            $pSum = (float) ($posData[$dateStr] ?? 0);
            $combined[$dateStr] = $dSum + $pSum;
            $cursor->addDay();
        }

        return $combined;
    }

    public function chartData(?int $productId, string $range, ?string $customFrom = null, ?string $customTo = null): array
    {
        $isCustom = ($range === 'custom' && $customFrom && $customTo);

        if ($isCustom) {
            $today = Carbon::parse($customFrom)->startOfDay();
            $horizonEnd = Carbon::parse($customTo)->endOfDay();
            $lookbackStart = $today->copy()->subDays(30);
            $daysInHorizon = $today->diffInDays($horizonEnd);
            $bucket = $daysInHorizon > 365 ? 'month' : ($daysInHorizon > 60 ? 'week' : 'day');
        } elseif ($range === '3months') {
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $lookbackStart = $currentMonthStart->copy()->subMonths(3)->startOfMonth();
            $horizonEnd = $currentMonthStart->copy()->addMonths(2)->endOfMonth();
            $bucket = 'month';
            $today = $now->startOfDay();
        } else {
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
            $bucket        = $config['bucket'];
        }

        $dailyData = $this->historicalDailySeries($productId, $lookbackStart, $horizonEnd);

        $labels = $historical = $forecast = $lowerBound = $upperBound = [];
        $cursor = $lookbackStart->copy();
        $forecastStep = 0;
        $lastHistory = null;
        $i = 0;

        if ($bucket === 'month') {
            $currentMonthStart = Carbon::now()->startOfMonth();
            $monthlyTotals = [];
            $tempCursor = $lookbackStart->copy();

            while ($tempCursor < $currentMonthStart) {
                $mStart = $tempCursor->copy()->startOfMonth();
                $mEnd = $tempCursor->copy()->endOfMonth();

                $monthSum = 0.0;
                $d = $mStart->copy();
                while ($d <= $mEnd) {
                    $monthSum += (float) ($dailyData[$d->toDateString()] ?? 0);
                    $d->addDay();
                }
                $monthlyTotals[] = $monthSum;
                $tempCursor->addMonthNoOverflow()->startOfMonth();
            }

            $avgMonthly = count($monthlyTotals) > 0
                ? (array_sum($monthlyTotals) / count($monthlyTotals))
                : 0;

            $stddev = 0.0;
            if (count($monthlyTotals) > 1) {
                $mean = array_sum($monthlyTotals) / count($monthlyTotals);
                $variance = 0.0;
                foreach ($monthlyTotals as $v) {
                    $variance += pow($v - $mean, 2);
                }
                $stddev = sqrt($variance / count($monthlyTotals));
            } elseif ($avgMonthly > 0) {
                $stddev = $avgMonthly * 0.2;
            }

            while ($cursor <= $horizonEnd) {
                $bucketStart = $cursor->copy()->startOfMonth();
                $bucketEnd = $cursor->copy()->endOfMonth();
                if ($bucketEnd > $horizonEnd) {
                    $bucketEnd = $horizonEnd->copy();
                }

                $labels[] = $cursor->format('M');

                if ($bucketStart < $currentMonthStart) {
                    $sum = 0.0;
                    $d = $bucketStart->copy();
                    while ($d <= $bucketEnd) {
                        $sum += (float) ($dailyData[$d->toDateString()] ?? 0);
                        $d->addDay();
                    }
                    $historical[] = (int) round($sum);
                    $forecast[]   = null;
                    $lowerBound[] = null;
                    $upperBound[] = null;
                    $lastHistory  = $i;
                } else {
                    $forecastStep++;
                    $expected = (int) round($avgMonthly);
                    $margin = 1.96 * $stddev * sqrt(0.25 * $forecastStep);
                    $historical[] = null;
                    $forecast[]   = $expected;
                    $lowerBound[] = (int) max(0, round($expected - $margin));
                    $upperBound[] = (int) round($expected + $margin);
                }

                $cursor->addMonthNoOverflow()->startOfMonth();
                $i++;
            }
        } else {
            $avgDaily = $productId
                ? $this->averageDailyDemand(Product::findOrFail($productId), $today)
                : $this->totalAverageDailyDemand($today);

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

            while ($cursor <= $horizonEnd) {
                if ($bucket === 'day') {
                    $bucketEnd  = $cursor->copy();
                    $label      = $cursor->format('M d');
                    $nextCursor = $cursor->copy()->addDay();
                    $stepDays   = 1;
                } else {
                    $bucketEnd  = $cursor->copy()->addDays(6);
                    if ($bucketEnd > $horizonEnd) {
                        $bucketEnd = $horizonEnd->copy();
                    }
                    $label      = $cursor->format('M d');
                    $nextCursor = $cursor->copy()->addDays(7);
                    $stepDays   = 7;
                }

                $labels[] = $label;

                if ($cursor <= $today) {
                    $sum = 0.0;
                    $d = $cursor->copy();
                    while ($d <= $bucketEnd && $d <= $today) {
                        $sum += (float) ($dailyData[$d->toDateString()] ?? 0);
                        $d->addDay();
                    }
                    $historical[] = $sum;
                    $forecast[]   = null;
                    $lowerBound[] = null;
                    $upperBound[] = null;
                    $lastHistory  = $i;
                } else {
                    $forecastStep++;
                    $expected     = $avgDaily * $stepDays;
                    $margin       = 1.96 * $stddev * sqrt($stepDays * $forecastStep);
                    $historical[] = null;
                    $forecast[]   = (int) round($expected);
                    $lowerBound[] = (int) max(0, round($expected - $margin));
                    $upperBound[] = (int) round($expected + $margin);
                }

                $cursor = $nextCursor;
                $i++;
            }
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
            'pivot_label'  => $isCustom ? 'FORECAST START' : 'TODAY',
        ];
    }
}