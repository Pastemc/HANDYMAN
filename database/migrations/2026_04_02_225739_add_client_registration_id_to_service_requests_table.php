<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('client_registration_id')->nullable()->after('id');
            $table->foreign('client_registration_id')
                  ->references('id')
                  ->on('client_registrations')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['client_registration_id']);
            $table->dropColumn('client_registration_id');
        });
    }
};