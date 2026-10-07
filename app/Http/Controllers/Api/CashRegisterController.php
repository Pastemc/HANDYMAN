<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = CashRegister::with('user');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $registers = $query->orderBy('id', 'desc')->paginate(15);

        return response()->json($registers);
    }

    public function getCurrentRegister(Request $request)
{
    try {
        $user = $request->user();
        
        $register = CashRegister::where('user_id', $user->id)
            ->where('status', 'open')
            ->with(['payments' => function($query) {
                $query->with(['serviceRequest.client', 'serviceRequest.handyman']);
            }])
            ->latest()
            ->first();

        // IMPORTANTE: Si no hay registro, devolver NULL, no un objeto vacío
        if (!$register) {
            return response()->json(null, 200);
        }

        // ASEGURARSE de que el ID existe
        if (!$register->id) {
            return response()->json(null, 200);
        }

        return response()->json($register, 200);

    } catch (\Exception $e) {
        Log::error('Error fetching current register: ' . $e->getMessage());
        return response()->json(null, 200);
    }
}

    public function open(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();

            // Verificar que no haya una caja abierta
            $openRegister = CashRegister::where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if ($openRegister) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Ya tienes una caja abierta. Debes cerrarla antes de abrir una nueva.'
                ], 400);
            }

            $register = CashRegister::create([
                'user_id' => $user->id,
                'opening_balance' => $request->opening_balance,
                'notes' => $request->notes,
                'opened_at' => now(),
                'status' => 'open',
            ]);

            DB::commit();

            Log::info('Cash register opened', ['id' => $register->id, 'user' => $user->id]);

            return response()->json([
                'message' => 'Caja abierta exitosamente',
                'cash_register' => $register
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error opening cash register: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al abrir caja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function close(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'closing_balance' => 'required|numeric|min:0',
            'expected_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $register = CashRegister::find($id);

            if (!$register) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Caja no encontrada'
                ], 404);
            }

            // Verificar que la caja pertenezca al usuario actual
            $user = $request->user();
            if ($register->user_id !== $user->id) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No tienes permiso para cerrar esta caja'
                ], 403);
            }

            // Verificar que la caja esté abierta
            if ($register->status === 'closed') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Esta caja ya está cerrada'
                ], 400);
            }

            // Calcular balance esperado
            $totalPayments = $register->payments()->sum('amount') ?? 0;
            $expectedBalance = floatval($register->opening_balance) + floatval($totalPayments);
            $difference = floatval($request->closing_balance) - $expectedBalance;

            // Actualizar el registro
            $register->closing_balance = $request->closing_balance;
            $register->expected_balance = $expectedBalance;
            $register->difference = $difference;
            $register->notes = $request->notes;
            $register->closed_at = now();
            $register->status = 'closed';
            $register->save();

            DB::commit();

            Log::info('Cash register closed', [
                'id' => $register->id,
                'user' => $user->id,
                'difference' => $difference
            ]);

            return response()->json([
                'message' => 'Caja cerrada exitosamente',
                'cash_register' => $register->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing cash register: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al cerrar caja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}