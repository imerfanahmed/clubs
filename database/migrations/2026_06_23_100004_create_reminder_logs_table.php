<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subscription_id');
            $table->string('type')->default('pre_renewal');
            $table->timestamp('period_end');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'period_end', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
