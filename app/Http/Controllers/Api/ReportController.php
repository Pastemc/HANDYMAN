<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Handyman;
use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if (!$startDate || !$endDate) {
                $endDate = now()->format('Y-m-d');
                $startDate = now()->subDays(30)->format('Y-m-d');
            }

            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();

            // RESUMEN
            $totalUsers = User::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
            $totalHandymen = Handyman::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
            $totalRequests = ServiceRequest::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
            
            $completedRequests = ServiceRequest::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed')
                ->count();
            
            $pendingRequests = ServiceRequest::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('status', 'pending')
                ->count();
            
            $totalRevenue = Payment::where('status', 'completed')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->sum('amount');
            
            $platformRevenue = $totalRevenue * 0.15; // 15% comisión
            
            $totalTransactions = Payment::where('status', 'completed')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->sum('amount');
            
            $averageRating = Review::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->avg('rating');

            // MÉTRICAS
            $activeUsers = ServiceRequest::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->distinct('client_id')
                ->count('client_id');
            
            $approvedHandymen = Handyman::where('approval_status', 'approved')->count();
            $pendingApproval = Handyman::where('approval_status', 'pending')->count();
            $totalCategories = ServiceCategory::count();
            $clientSatisfaction = $averageRating;

            // SERVICIOS MÁS SOLICITADOS
            $mostRequestedServices = ServiceRequest::select(
                'service_categories.name as category_name',
                DB::raw('COUNT(service_requests.id) as total_requests'),
                DB::raw('COALESCE(SUM(service_requests.final_cost), 0) as total_revenue'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as average_rating')
            )
            ->join('service_categories', 'service_requests.service_category_id', '=', 'service_categories.id')
            ->leftJoin('reviews', 'service_requests.id', '=', 'reviews.service_request_id')
            ->whereBetween('service_requests.created_at', [$startDateTime, $endDateTime])
            ->groupBy('service_categories.id', 'service_categories.name')
            ->orderBy('total_requests', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'category_name' => $item->category_name,
                    'total_requests' => $item->total_requests,
                    'total_revenue' => number_format($item->total_revenue, 2, '.', ''),
                    'average_rating' => number_format($item->average_rating, 1, '.', ''),
                ];
            });

            return response()->json([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_users' => $totalUsers,
                    'total_handymen' => $totalHandymen,
                    'total_requests' => $totalRequests,
                    'completed_requests' => $completedRequests,
                    'pending_requests' => $pendingRequests,
                    'total_revenue' => number_format($totalRevenue, 2, '.', ''),
                    'total_transactions' => number_format($totalTransactions, 2, '.', ''),
                    'platform_revenue' => number_format($platformRevenue, 2, '.', ''),
                    'average_rating' => number_format($averageRating ?? 0, 1, '.', ''),
                ],
                'metrics' => [
                    'active_users' => $activeUsers,
                    'approved_handymen' => $approvedHandymen,
                    'pending_approval' => $pendingApproval,
                    'total_categories' => $totalCategories,
                    'client_satisfaction' => number_format($clientSatisfaction ?? 0, 1, '.', ''),
                ],
                'most_requested_services' => $mostRequestedServices,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar reporte',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            $lang = $request->input('lang', 'es');

            $data = $this->getReportData($startDate, $endDate);
            $translations = $this->getTranslations($lang);

            // Por ahora retornar CSV como alternativa
            return $this->exportExcel($request);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            $lang = $request->input('lang', 'es');

            $data = $this->getReportData($startDate, $endDate);
            $translations = $this->getTranslations($lang);

            $filename = 'reporte_' . $startDate . '_' . $endDate . '.csv';
            $handle = fopen('php://temp', 'r+');

            // BOM UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado
            fputcsv($handle, [$translations['heavens_home_report']]);
            fputcsv($handle, [$translations['period'] . ': ' . $startDate . ' - ' . $endDate]);
            fputcsv($handle, []);

            // Resumen
            fputcsv($handle, [$translations['period_summary']]);
            fputcsv($handle, [$translations['start_date'], $startDate]);
            fputcsv($handle, [$translations['end_date'], $endDate]);
            fputcsv($handle, [$translations['total_revenue'], '$' . number_format($data['summary']['total_revenue'], 2)]);
            fputcsv($handle, [$translations['platform_revenue'], '$' . number_format($data['summary']['platform_revenue'], 2)]);
            fputcsv($handle, [$translations['total_jobs'], $data['summary']['total_jobs']]);
            fputcsv($handle, [$translations['average_rating'], number_format($data['summary']['average_rating'], 2) . '/5.0']);
            fputcsv($handle, []);

            // Servicios más solicitados
            fputcsv($handle, [$translations['top_services']]);
            fputcsv($handle, [
                $translations['category'], 
                $translations['total_requests'], 
                $translations['total_revenue'], 
                $translations['average_rating']
            ]);
            
            foreach ($data['most_requested_services'] as $service) {
                fputcsv($handle, [
                    $service['category_name'],
                    $service['total_requests'],
                    '$' . number_format($service['total_revenue'], 2),
                    number_format($service['average_rating'], 2),
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getReportData($startDate, $endDate)
    {
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $totalRevenue = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');
        
        $platformRevenue = $totalRevenue * 0.15;

        $totalJobs = ServiceRequest::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
        
        $averageRating = Review::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->avg('rating') ?? 0;

        $mostRequestedServices = ServiceRequest::select(
            'service_categories.name as category_name',
            DB::raw('COUNT(service_requests.id) as total_requests'),
            DB::raw('COALESCE(SUM(service_requests.final_cost), 0) as total_revenue'),
            DB::raw('COALESCE(AVG(reviews.rating), 0) as average_rating')
        )
        ->join('service_categories', 'service_requests.service_category_id', '=', 'service_categories.id')
        ->leftJoin('reviews', 'service_requests.id', '=', 'reviews.service_request_id')
        ->whereBetween('service_requests.created_at', [$startDateTime, $endDateTime])
        ->groupBy('service_categories.id', 'service_categories.name')
        ->orderBy('total_requests', 'desc')
        ->limit(10)
        ->get()
        ->map(function($item) {
            return [
                'category_name' => $item->category_name,
                'total_requests' => $item->total_requests,
                'total_revenue' => $item->total_revenue,
                'average_rating' => $item->average_rating,
            ];
        });

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'platform_revenue' => $platformRevenue,
                'total_jobs' => $totalJobs,
                'average_rating' => $averageRating,
            ],
            'most_requested_services' => $mostRequestedServices,
        ];
    }

    private function getTranslations($lang)
    {
        $translations = [
            'es' => [
                'heavens_home_report' => 'REPORTE DE HEAVEN\'S HOME',
                'period' => 'Período',
                'period_summary' => 'Resumen del Período',
                'start_date' => 'Fecha Inicio',
                'end_date' => 'Fecha Fin',
                'total_revenue' => 'Ingresos Totales',
                'platform_revenue' => 'Ingresos Plataforma (15%)',
                'total_jobs' => 'Trabajos Totales',
                'average_rating' => 'Calificación Promedio',
                'top_services' => 'Servicios Más Solicitados',
                'category' => 'Categoría',
                'total_requests' => 'Total Solicitudes',
            ],
            'en' => [
                'heavens_home_report' => 'HEAVEN\'S HOME REPORT',
                'period' => 'Period',
                'period_summary' => 'Period Summary',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'total_revenue' => 'Total Revenue',
                'platform_revenue' => 'Platform Revenue (15%)',
                'total_jobs' => 'Total Jobs',
                'average_rating' => 'Average Rating',
                'top_services' => 'Top Services',
                'category' => 'Category',
                'total_requests' => 'Total Requests',
            ],
        ];

        return $translations[$lang] ?? $translations['es'];
    }
}