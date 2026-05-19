<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $fillable = [
        'month', 'year', 'revenue', 'expense', 'cashflow', 'note'
    ];
}
