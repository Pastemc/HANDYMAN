<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('handyman_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'transfer'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices
            $table->index('payment_number');
            $table->index('service_request_id');
            $table->index('cash_register_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};