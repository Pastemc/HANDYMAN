<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handyman extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'hourly_rate',
        'years_experience',
        'certification',
        'availability_status',
        'rating',
        'total_jobs',
        'approval_status',
        'approved_at',
    ];

    protected $casts = [
        'skills' => 'array',
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceCategories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'handyman_service_category');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}