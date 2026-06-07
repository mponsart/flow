<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReport extends Model
{
    protected $fillable = [
        'type', 'title', 'content', 'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'summary' => 'Résumé financier',
            'analysis' => 'Analyse approfondie',
            'anomalies' => 'Détection d\'anomalies',
            default => $this->type,
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'summary' => 'bg-indigo-900 text-indigo-300',
            'analysis' => 'bg-teal-900 text-teal-300',
            'anomalies' => 'bg-red-900 text-red-300',
            default => 'bg-zinc-700 text-zinc-300',
        };
    }
}
