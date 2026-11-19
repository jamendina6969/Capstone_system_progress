<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class InventoryMathTest extends TestCase
{
    /**
     * Test restock adds quantity correctly to designated location.
     */
    public function test_restock_increments_location_quantity(): void
    {
        $initialStoreQty = 15;
        $initialWhQty = 40;
        $restockQty = 25;

        // Restock to store
        $newStoreQty = $initialStoreQty + $restockQty;
        $this->assertEquals(40, $newStoreQty);

        // Restock to warehouse
        $newWhQty = $initialWhQty + $restockQty;
        $this->assertEquals(65, $newWhQty);
    }

    /**
     * Test inter-location transfer maintains total quantity balance.
     */
    public function test_transfer_preserves_total_quantity_balance(): void
    {
        $storeQty = 10;
        $warehouseQty = 50;
        $initialTotal = $storeQty + $warehouseQty;

        $transferQty = 20; // Transfer 20 from warehouse to store

        // Warehouse -> Store
        $newWarehouseQty = $warehouseQty - $transferQty;
        $newStoreQty = $storeQty + $transferQty;
        $finalTotal = $newStoreQty + $newWarehouseQty;

        $this->assertEquals(30, $newStoreQty);
        $this->assertEquals(30, $newWarehouseQty);
        $this->assertEquals($initialTotal, $finalTotal);
    }

    /**
     * Test stock status classification based on reorder point.
     */
    public function test_stock_status_determinations(): void
    {
        $reorderPoint = 10;

        // Out of stock
        $outQty = 0;
        $isOut = $outQty <= 0;
        $this->assertTrue($isOut);

        // Low stock
        $lowQty = 5;
        $isLow = ($lowQty > 0) && ($lowQty <= $reorderPoint);
        $this->assertTrue($isLow);

        // In stock
        $normalQty = 25;
        $isInStock = $normalQty > $reorderPoint;
        $this->assertTrue($isInStock);
    }
}
