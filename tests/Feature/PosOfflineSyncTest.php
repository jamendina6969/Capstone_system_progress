<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PosOfflineSyncTest extends TestCase
{
    public function test_offline_sync_route_registered(): void
    {
        $this->assertTrue(Route::has('pos.syncOffline'));
    }

    public function test_offline_sync_requires_authentication(): void
    {
        $response = $this->post(route('pos.syncOffline'), [
            'orders' => []
        ]);

        $response->assertRedirect('/login');
    }
}
