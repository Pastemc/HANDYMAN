<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_registrations', function (Blueprint $table) {
            $table->dropUnique(['email']); // Eliminar unique
        });
    }

    public function down()
    {
        Schema::table('client_registrations', function (Blueprint $table) {
            $table->unique('email'); // Restaurar si hacemos rollback
        });
    }
};