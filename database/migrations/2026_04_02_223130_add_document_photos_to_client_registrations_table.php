<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_registrations', function (Blueprint $table) {
            // Eliminar columna antigua si existe
            if (Schema::hasColumn('client_registrations', 'document_photo')) {
                $table->dropColumn('document_photo');
            }
            
            // Agregar nueva columna para múltiples fotos
            if (!Schema::hasColumn('client_registrations', 'document_photos')) {
                $table->json('document_photos')->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('client_registrations', 'document_photos')) {
                $table->dropColumn('document_photos');
            }
        });
    }
};