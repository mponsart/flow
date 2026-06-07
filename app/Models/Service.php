<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'type', 'price', 'cost', 'status', 'description'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
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
        return (float) $this->subscriptions()
            ->with('revenues')
            ->get()
            ->flatMap->revenues
            ->sum('amount');
    }

    public function getSubscriberCountAttribute(): int
    {
        return $this->activeSubscriptions()->count();
    }

    public function getMonthlyRevenueAttribute(): float
    {
        $monthly = $this->activeSubscriptions()->where('cycle', 'monthly')->count() * $this->price;
        $annual = $this->activeSubscriptions()->where('cycle', 'annual')->count() * ($this->price / 12);
        return $monthly + $annual;
    }
}
