<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('document_type'); // dni, passport, other
            $table->string('document_number')->unique();
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->text('message')->nullable();
            $table->string('document_photo')->nullable(); // ← NUEVO CAMPO
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable(); // ← NUEVO CAMPO
            $table->timestamps();

            $table->foreign('service_category_id')
                ->references('id')
                ->on('service_categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_registrations');
    }
};