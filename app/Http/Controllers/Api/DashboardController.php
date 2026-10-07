<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Handyman;
use App\Models\ServiceRequest;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Si es admin o superadmin
        if ($user && $user->hasAnyRole(['admin', 'superadmin'])) {
            return $this->adminDashboard();
        }

        // Si es handyman
        if ($user && $user->hasRole('handyman')) {
            return $this->handymanDashboard($user);
        }

        // Si es cliente
        if ($user && $user->hasRole('client')) {
            return $this->clientDashboard($user);
        }

        return response()->json(['message' => 'No autorizado'], 403);
    }

    /**
     * Dashboard para administradores
     */
    private function adminDashboard()
    {
        // Calcular estadísticas
        $totalUsers = User::count();
        $registeredHandymen = Handyman::count();
        $pendingHandymen = Handyman::where('approval_status', 'pending')->count();
        
        $totalRequests = ServiceRequest::count();
        $pendingRequests = ServiceRequest::where('status', 'pending')->count();
        $completedRequests = ServiceRequest::where('status', 'completed')->count();
        
        $totalTransactions = Payment::where('status', 'completed')->sum('amount');
        $platformRevenue = $totalTransactions * 0.15; // 15% comisión

        $stats = [
            'total_users' => $totalUsers,
            'registered_handymen' => $registeredHandymen,
            'pending_handymen' => $pendingHandymen,
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'completed_requests' => $completedRequests,
            'total_transactions' => number_format($totalTransactions, 2, '.', ''),
            'platform_revenue' => number_format($platformRevenue, 2, '.', ''),
        ];

        // Top 5 handymen por rating
        $topHandymen = Handyman::with('user')
            ->where('rating', '>', 0)
            ->orderBy('rating', 'desc')
            ->orderBy('total_jobs', 'desc')
            ->limit(5)
            ->get();

        // Últimas 5 solicitudes
        $recentRequests = ServiceRequest::with(['client', 'handyman', 'serviceCategory'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'top_handymen' => $topHandymen,
            'recent_requests' => $recentRequests,
        ]);
    }

    /**
     * Dashboard para handymen
     */
    private function handymanDashboard($user)
    {
        $handyman = Handyman::where('user_id', $user->id)->first();

        $totalJobs = ServiceRequest::where('handyman_id', $user->id)->count();
        $pendingJobs = ServiceRequest::where('handyman_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $completedJobs = ServiceRequest::where('handyman_id', $user->id)
            ->where('status', 'completed')
            ->count();
        
        $totalEarnings = Payment::whereHas('serviceRequest', function($query) use ($user) {
            $query->where('handyman_id', $user->id);
        })
        ->where('status', 'completed')
        ->sum('amount');

        $stats = [
            'total_jobs' => $totalJobs,
            'pending_jobs' => $pendingJobs,
            'completed_jobs' => $completedJobs,
            'total_earnings' => number_format($totalEarnings, 2, '.', ''),
            'rating' => $handyman ? $handyman->rating : 0,
        ];

        $recentJobs = ServiceRequest::with(['client', 'serviceCategory'])
            ->where('handyman_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_jobs' => $recentJobs,
        ]);
    }

    /**
     * Dashboard para clientes
     */
    private function clientDashboard($user)
    {
        $totalRequests = ServiceRequest::where('client_id', $user->id)->count();
        $pendingRequests = ServiceRequest::where('client_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $inProgressRequests = ServiceRequest::where('client_id', $user->id)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->count();
        $completedRequests = ServiceRequest::where('client_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $stats = [
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'in_progress_requests' => $inProgressRequests,
            'completed_requests' => $completedRequests,
        ];

        $recentRequests = ServiceRequest::with(['handyman', 'serviceCategory', 'review'])
            ->where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_requests' => $recentRequests,
        ]);
    }
}