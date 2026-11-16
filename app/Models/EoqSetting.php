<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditsChanges;

class EoqSetting extends Model
{
    use AuditsChanges;

    protected $fillable = [
        'default_ordering_cost',
        'ordering_cost_percentage',
        'holding_cost_percentage',
        'holding_cost_per_cbm',
        'updated_by',
    ];
}