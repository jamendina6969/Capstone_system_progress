<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PosCalculationTest extends TestCase
{
    /**
     * Test basic subtotal and 12% VAT calculation.
     */
    public function test_vat_and_total_calculation_without_discounts(): void
    {
        $item1Price = 100.00;
        $item1Qty = 2; // 200.00
        $item2Price = 50.00;
        $item2Qty = 1; // 50.00

        $subtotal = ($item1Price * $item1Qty) + ($item2Price * $item2Qty); // 250.00
        $vatRate = 0.12;
        $vatAmount = round($subtotal * $vatRate, 2); // 30.00
        $grandTotal = $subtotal + $vatAmount; // 280.00

        $this->assertEquals(250.00, $subtotal);
        $this->assertEquals(30.00, $vatAmount);
        $this->assertEquals(280.00, $grandTotal);
    }

    /**
     * Test percentage cart discount calculation.
     */
    public function test_percentage_cart_discount_calculation(): void
    {
        $subtotal = 1000.00;
        $discountPercent = 10; // 10% off

        $discountAmount = round($subtotal * ($discountPercent / 100), 2); // 100.00
        $taxableAmount = max(0, $subtotal - $discountAmount); // 900.00
        $vat = round($taxableAmount * 0.12, 2); // 108.00
        $grandTotal = $taxableAmount + $vat; // 1008.00

        $this->assertEquals(100.00, $discountAmount);
        $this->assertEquals(900.00, $taxableAmount);
        $this->assertEquals(108.00, $vat);
        $this->assertEquals(1008.00, $grandTotal);
    }

    /**
     * Test fixed amount cart discount.
     */
    public function test_fixed_amount_cart_discount_capped_at_subtotal(): void
    {
        $subtotal = 150.00;
        $fixedDiscount = 200.00; // Greater than subtotal

        $effectiveDiscount = min($subtotal, $fixedDiscount); // Capped at 150.00
        $taxableAmount = max(0, $subtotal - $effectiveDiscount); // 0.00
        $vat = round($taxableAmount * 0.12, 2); // 0.00
        $grandTotal = $taxableAmount + $vat; // 0.00

        $this->assertEquals(150.00, $effectiveDiscount);
        $this->assertEquals(0.00, $grandTotal);
    }

    /**
     * Test custom project cost calculation with materials, labor, installation, outsource, and markup.
     */
    public function test_custom_project_pricing_breakdown(): void
    {
        $materialsCost = 1500.00;
        $outsourceCost = 500.00;
        $laborCost = 800.00;
        $installationCost = 600.00;
        $additionalCost = 100.00;
        $freightCost = 250.00;

        $projectSubtotal = $materialsCost + $outsourceCost + $laborCost + $installationCost + $additionalCost + $freightCost;
        $this->assertEquals(3750.00, $projectSubtotal);

        // 20% markup
        $markupPercent = 20;
        $markupAmount = round($projectSubtotal * ($markupPercent / 100), 2); // 750.00
        $sellingPriceBeforeTax = $projectSubtotal + $markupAmount; // 4500.00
        $vat = round($sellingPriceBeforeTax * 0.12, 2); // 540.00
        $finalProjectTotal = $sellingPriceBeforeTax + $vat; // 5040.00

        $this->assertEquals(750.00, $markupAmount);
        $this->assertEquals(4500.00, $sellingPriceBeforeTax);
        $this->assertEquals(540.00, $vat);
        $this->assertEquals(5040.00, $finalProjectTotal);
    }
}
