<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'images',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'images' => 'array',
    ];

    protected $appends = ['images_urls'];

    public function getImagesUrlsAttribute()
    {
        if ($this->images && is_array($this->images)) {
            return array_map(function($image) {
                return Storage::url($image);
            }, $this->images);
        }
        return [];
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function handymen()
    {
        return $this->belongsToMany(Handyman::class, 'handyman_service_category');
    }
}