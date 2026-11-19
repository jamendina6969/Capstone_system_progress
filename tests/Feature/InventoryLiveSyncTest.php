<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InventoryLiveSyncTest extends TestCase
{
    public function test_live_sync_requires_authenticated_role(): void
    {
        $response = $this->get(route('inventory.liveSync'));
        $response->assertRedirect('/login');
    }

    public function test_live_sync_endpoint_registered(): void
    {
        $this->assertTrue(Route::has('inventory.liveSync'));
    }
}
