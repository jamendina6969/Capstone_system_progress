<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EoqMathTest extends TestCase
{
    /**
     * Test standard Economic Order Quantity formula: sqrt((2 * D * S) / H)
     * D = Annual Demand (units)
     * S = Ordering Cost per order (currency)
     * H = Holding Cost per unit per year (currency)
     */
    public function test_standard_eoq_formula_calculation(): void
    {
        $annualDemand = 1000;
        $orderingCost = 50.00;
        $holdingCost = 2.50;

        $numerator = 2 * $annualDemand * $orderingCost; // 100,000
        $eoq = sqrt($numerator / $holdingCost); // sqrt(40,000) = 200

        $this->assertEquals(200.0, $eoq);
    }

    /**
     * Test EOQ handling when demand or costs are zero.
     */
    public function test_eoq_zero_demand_or_zero_holding_cost(): void
    {
        $annualDemand = 0;
        $orderingCost = 50.00;
        $holdingCost = 2.50;

        $eoq = $holdingCost > 0 && $annualDemand > 0
            ? sqrt((2 * $annualDemand * $orderingCost) / $holdingCost)
            : 0;

        $this->assertEquals(0, $eoq);
    }

    /**
     * Test holding cost derived from percentage of purchase cost.
     */
    public function test_holding_cost_percentage_calculation(): void
    {
        $purchaseCost = 450.00;
        $holdingCostPercent = 20; // 20% per year

        $annualHoldingCostPerUnit = round($purchaseCost * ($holdingCostPercent / 100), 2);
        $this->assertEquals(90.00, $annualHoldingCostPerUnit);
    }

    /**
     * Test reorder point calculation: (Daily Demand * Lead Time) + Safety Stock.
     */
    public function test_reorder_point_calculation(): void
    {
        $dailyDemand = 10; // units/day
        $leadTimeDays = 5; // days
        $safetyStock = 15; // units

        $reorderPoint = ($dailyDemand * $leadTimeDays) + $safetyStock;
        $this->assertEquals(65, $reorderPoint);
    }
}
