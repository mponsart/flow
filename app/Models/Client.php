<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'company', 'phone', 'status', 'notes'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class)->where('status', 'actif');
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->revenues()->sum('amount');
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function getNetProfitAttribute(): float
    {
        return $this->total_revenue - $this->total_expenses;
    }
}
