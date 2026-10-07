<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'service_request_id',
        'cash_register_id',
        'amount',
        'payment_method',
        'status',
        'transaction_date',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Relación con la solicitud de servicio
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Relación con la caja registradora
     */
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Scope para obtener solo pagos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para obtener pagos de un método específico
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}