<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->enum('type', ['email', 'sms', 'whatsapp']);
            $table->enum('stage', ['h_minus_7', 'h_minus_3', 'h_minus_1', 'h_plus_1', 'pre_soft_limit', 'pre_suspend']);
            $table->timestamp('sent_at');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
