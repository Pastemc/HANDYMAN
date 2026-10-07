<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'client_id',
        'handyman_id',
        'rating',
        'comment',
        'images',
        'is_approved',
    ];

    protected $casts = [
        'images' => 'array',
        'is_approved' => 'boolean',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function handyman()
    {
        return $this->belongsTo(User::class, 'handyman_id');
    }
}