<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'type', 'price', 'cost', 'status', 'description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
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
        return (float) Revenue::whereIn('subscription_id', $this->subscriptions()->pluck('id'))->sum('amount');
    }

    public function getSubscriberCountAttribute(): int
    {
        return $this->activeSubscriptions()->count();
    }

    public function getMonthlyRevenueAttribute(): float
    {
        $monthly = $this->activeSubscriptions()->where('cycle', 'monthly')->count() * (float) $this->price;
        $annual = $this->activeSubscriptions()->where('cycle', 'annual')->count() * ((float) $this->price / 12);
        return $monthly + $annual;
    }
}
