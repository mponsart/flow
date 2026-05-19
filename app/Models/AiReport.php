<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReport extends Model
{
    protected $fillable = [
        'summary', 'alerts', 'anomalies', 'advice', 'period', 'type'
    ];
}
