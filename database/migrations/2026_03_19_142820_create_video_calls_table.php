<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'ended', 'missed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable(); // segundos
            $table->timestamps();

            $table->index(['caller_id', 'receiver_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_calls');
    }
};