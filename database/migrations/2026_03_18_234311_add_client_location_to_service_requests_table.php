<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->decimal('client_latitude', 10, 8)->nullable()->after('address');
            $table->decimal('client_longitude', 11, 8)->nullable()->after('client_latitude');
        });
    }

    public function down()
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['client_latitude', 'client_longitude']);
        });
    }
};