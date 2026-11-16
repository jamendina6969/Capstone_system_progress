<?php

namespace App\Observers;

use App\Models\SalesRecord;
use App\Services\DemandService;

class SalesRecordObserver
{
    protected DemandService $demandService;

    public function __construct(DemandService $demandService)
    {
        $this->demandService = $demandService;
    }

    public function created(SalesRecord $salesRecord): void
    {
        $this->demandService->refreshForSale($salesRecord);
    }

    public function updated(SalesRecord $salesRecord): void
    {
        $this->demandService->refreshForSale($salesRecord);
    }

    public function deleted(SalesRecord $salesRecord): void
    {
        $this->demandService->refreshForSale($salesRecord);
    }
}