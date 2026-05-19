<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'type', 'price', 'monthly_cost', 'annual_cost', 'status'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
