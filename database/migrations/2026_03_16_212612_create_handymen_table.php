<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handymen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->integer('years_experience')->default(0);
            $table->string('certification')->nullable();
            $table->enum('availability_status', ['available', 'busy', 'offline'])->default('offline');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_jobs')->default(0);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('handyman_service_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handyman_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handyman_service_category');
        Schema::dropIfExists('handymen');
    }
};