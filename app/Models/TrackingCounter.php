<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingCounter extends Model
{
    protected $table = 'tracking_counters';

    protected $fillable = [
        'office_code',
        'year',
        'month',
        'last_sequence',
    ];

    protected $casts = [
        'year'          => 'integer',
        'month'         => 'integer',
        'last_sequence' => 'integer',
    ];
}
