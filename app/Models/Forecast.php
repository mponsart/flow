<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $fillable = [
        'month', 'projected_revenue', 'projected_expenses', 'projected_profit',
        'actual_revenue', 'actual_expenses', 'notes'
    ];

    protected $casts = [
        'month' => 'date',
        'projected_revenue' => 'decimal:2',
        'projected_expenses' => 'decimal:2',
        'projected_profit' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'actual_expenses' => 'decimal:2',
    ];

    public function getActualProfitAttribute(): ?float
    {
        if ($this->actual_revenue === null) return null;
        return (float) $this->actual_revenue - (float) ($this->actual_expenses ?? 0);
    }
}
