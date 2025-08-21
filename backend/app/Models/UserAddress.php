<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'name',
        'phone',
        'address',
        'province',
        'city',
        'subdistrict',
        'postal_code',
        'province_id',
        'city_id',
        'subdistrict_id',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->subdistrict}, {$this->city}, {$this->province} {$this->postal_code}";
    }
}
