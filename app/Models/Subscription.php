<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'client_id', 'service_id', 'cycle', 'start_date', 'end_date', 'auto_renewal', 'status', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renewal' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function getMonthlyAmountAttribute(): float
    {
        if (!$this->service) return 0;
        if ($this->cycle === 'monthly') return (float) $this->service->price;
        return (float) $this->service->price / 12;
    }
}
