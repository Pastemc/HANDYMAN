<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'client_id',
        'client_registration_id',
        'handyman_id',
        'service_category_id',
        'title',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'preferred_date',
        'preferred_time',
        'status',
        'priority',
        'photos',
        'estimated_cost',
        'final_cost',
        'started_at',
        'completed_at',
        'cancelled_at',
        'temp_client_name',
        'temp_client_email',
        'temp_client_phone',
        'handyman_notes',
        'client_notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'preferred_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = ['photos_urls'];

    // ========== RELATIONSHIPS ==========
    
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function clientRegistration()
    {
        return $this->belongsTo(ClientRegistration::class, 'client_registration_id');
    }

    public function handyman()
    {
        return $this->belongsTo(User::class, 'handyman_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // ========== ACCESSORS ==========
    
    /**
     * Get full URLs for photos
     */
    public function getPhotosUrlsAttribute()
    {
        if (!$this->photos || !is_array($this->photos)) {
            return [];
        }

        return array_map(function($photo) {
            return Storage::url($photo);
        }, $this->photos);
    }

    // ========== SCOPES ==========
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ========== HELPERS ==========
    
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generate unique request number
     */
    public static function generateRequestNumber()
    {
        $lastRequest = self::orderBy('id', 'desc')->first();
        
        if (!$lastRequest) {
            return 'REQ000001';
        }

        $lastNumber = (int) substr($lastRequest->request_number, 3);
        $newNumber = $lastNumber + 1;
        
        return 'REQ' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get client name (from client or registration)
     */
    public function getClientNameAttribute()
    {
        if ($this->client) {
            return $this->client->name;
        }
        
        if ($this->clientRegistration) {
            return $this->clientRegistration->name;
        }
        
        return $this->temp_client_name ?? 'N/A';
    }

    /**
     * Get client email (from client or registration)
     */
    public function getClientEmailAttribute()
    {
        if ($this->client) {
            return $this->client->email;
        }
        
        if ($this->clientRegistration) {
            return $this->clientRegistration->email;
        }
        
        return $this->temp_client_email ?? 'N/A';
    }

    /**
     * Get client phone (from client or registration)
     */
    public function getClientPhoneAttribute()
    {
        if ($this->client) {
            return $this->client->phone;
        }
        
        if ($this->clientRegistration) {
            return $this->clientRegistration->phone;
        }
        
        return $this->temp_client_phone ?? 'N/A';
    }
}