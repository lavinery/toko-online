<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'status',
        'attempts',
        'last_error',
        'processed_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function markAsProcessed()
    {
        $this->update([
            'status' => 'success',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'last_error' => $error,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
