<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Verificar si las columnas no existen antes de agregarlas
            if (!Schema::hasColumn('service_requests', 'temp_client_name')) {
                $table->string('temp_client_name')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('service_requests', 'temp_client_email')) {
                $table->string('temp_client_email')->nullable()->after('temp_client_name');
            }
            if (!Schema::hasColumn('service_requests', 'temp_client_phone')) {
                $table->string('temp_client_phone')->nullable()->after('temp_client_email');
            }
        });
    }

    public function down()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'temp_client_name')) {
                $table->dropColumn('temp_client_name');
            }
            if (Schema::hasColumn('service_requests', 'temp_client_email')) {
                $table->dropColumn('temp_client_email');
            }
            if (Schema::hasColumn('service_requests', 'temp_client_phone')) {
                $table->dropColumn('temp_client_phone');
            }
        });
    }
};