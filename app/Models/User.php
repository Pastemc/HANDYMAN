<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document_type',      // ✅ AGREGADO
        'document_number',    // ✅ AGREGADO
        'avatar',
        'address',
        'city',
        'state',
        'zip_code',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relación con roles (tabla user_role)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Relación con handyman profile
     */
    public function handyman()
    {
        return $this->hasOne(Handyman::class);
    }

    /**
     * Service requests donde el usuario es cliente
     */
    public function clientServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }

    /**
     * Service requests donde el usuario es handyman
     */
    public function handymanServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'handyman_id');
    }

    /**
     * Mensajes enviados
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Notificaciones del usuario
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Pagos como cliente
     */
    public function clientPayments()
    {
        return $this->hasMany(Payment::class, 'client_id');
    }

    /**
     * Pagos como handyman
     */
    public function handymanPayments()
    {
        return $this->hasMany(Payment::class, 'handyman_id');
    }

    /**
     * Reviews escritas por el usuario
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'client_id');
    }

    /**
     * Check if user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Assign a role to the user
     *
     * @param string $roleName
     * @return $this
     */
    public function assignRole($roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
        return $this;
    }

    /**
     * Remove a role from the user
     *
     * @param string $roleName
     * @return $this
     */
    public function removeRole($roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
        return $this;
    }

    /**
     * Sync roles for the user (removes old, adds new)
     *
     * @param array $roleNames
     * @return $this
     */
    public function syncRoles(array $roleNames)
    {
        $roleIds = \App\Models\Role::whereIn('name', $roleNames)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
        return $this;
    }

    /**
     * Get all role names for the user
     *
     * @return array
     */
    public function getRoleNames()
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is client
     *
     * @return bool
     */
    public function isClient()
    {
        return $this->hasRole('client');
    }

    /**
     * Check if user is handyman
     *
     * @return bool
     */
    public function isHandyman()
    {
        return $this->hasRole('handyman');
    }

    /**
     * Get user's full address
     *
     * @return string
     */
    public function getFullAddress()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get user's document display
     *
     * @return string
     */
    public function getDocumentDisplay()
    {
        if (!$this->document_type || !$this->document_number) {
            return 'No document';
        }

        return strtoupper($this->document_type) . ' - ' . $this->document_number;
    }

    /**
     * Scope to filter active users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by role
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $roleName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}