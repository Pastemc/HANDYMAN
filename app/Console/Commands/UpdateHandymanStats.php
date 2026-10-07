<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Handyman;
use App\Models\ServiceRequest;
use App\Models\Review;

class UpdateHandymanStats extends Command
{
    protected $signature = 'handymen:update-stats';
    protected $description = 'Actualizar estadísticas de handymen (trabajos y calificaciones)';

    public function handle()
    {
        $handymen = Handyman::all();

        foreach ($handymen as $handyman) {
            // Contar trabajos completados
            $completedJobs = ServiceRequest::where('handyman_id', $handyman->user_id)
                ->where('status', 'completed')
                ->count();
            
            // Calcular promedio de calificaciones
            $reviews = Review::where('handyman_id', $handyman->user_id)->get();
            $averageRating = $reviews->avg('rating');
            
            // Actualizar
            $handyman->update([
                'total_jobs' => $completedJobs,
                'rating' => $averageRating ? round($averageRating, 1) : 0,
            ]);

            $this->info("Handyman {$handyman->id}: {$completedJobs} trabajos, {$averageRating} estrellas");
        }

        $this->info('¡Estadísticas actualizadas!');
        return 0;
    }
}
