<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClientRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'document_type',
        'document_number',
        'address',
        'city',
        'state',
        'zip_code',
        'service_category_id',
        'message',
        'document_photos', // Array de fotos
        'status',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'document_photos' => 'array', // IMPORTANTE: Cast a array
    ];

    protected $appends = [
        'document_photos_urls'
    ];

    // Relación con categoría de servicio
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    // Accessor para URLs completas de las fotos
    public function getDocumentPhotosUrlsAttribute()
    {
        if (!$this->document_photos || !is_array($this->document_photos)) {
            return [];
        }

        return array_map(function ($photo) {
            return Storage::url($photo);
        }, $this->document_photos);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}