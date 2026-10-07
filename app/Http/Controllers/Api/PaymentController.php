<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['serviceRequest.client', 'serviceRequest.handyman', 'cashRegister']);

        // Filtros
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Si es handyman, solo ver sus pagos
        $user = $request->user();
        if ($user && $user->hasRole('handyman')) {
            $query->whereHas('serviceRequest', function($q) use ($user) {
                $q->where('handyman_id', $user->id);
            });
        }

        // Si es cliente, solo ver sus pagos
        if ($user && $user->hasRole('client')) {
            $query->whereHas('serviceRequest', function($q) use ($user) {
                $q->where('client_id', $user->id);
            });
        }

        $payments = $query->orderBy('id', 'desc')->paginate(15);

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card,debit_card,transfer',
            'status' => 'nullable|in:pending,completed,failed,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Obtener caja abierta
            $cashRegister = CashRegister::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            $amount = $request->amount;

            $payment = Payment::create([
                'payment_number' => 'PAY-' . strtoupper(Str::random(10)),
                'service_request_id' => $request->service_request_id,
                'cash_register_id' => $cashRegister ? $cashRegister->id : null,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => $request->status ?? 'completed',
                'transaction_date' => now(),
            ]);

            return response()->json([
                'message' => 'Pago registrado exitosamente',
                'payment' => $payment->load(['serviceRequest.client', 'serviceRequest.handyman'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $payment = Payment::with(['serviceRequest.client', 'serviceRequest.handyman', 'cashRegister'])
            ->findOrFail($id);

        return response()->json($payment);
    }

    public function platformRevenue(Request $request)
    {
        $totalTransactions = Payment::where('status', 'completed')->sum('amount');
        $totalPayments = Payment::where('status', 'completed')->count();

        return response()->json([
            'total_transactions' => number_format($totalTransactions, 2, '.', ''),
            'total_payments' => $totalPayments,
        ]);
    }

    public function earnings(Request $request)
    {
        $user = $request->user();
        
        $totalEarnings = Payment::whereHas('serviceRequest', function($query) use ($user) {
            $query->where('handyman_id', $user->id);
        })->where('status', 'completed')->sum('amount');

        return response()->json([
            'total_earnings' => number_format($totalEarnings, 2, '.', '')
        ]);
    }
}