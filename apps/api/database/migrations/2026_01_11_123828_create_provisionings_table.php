<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisionings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            $table->string('pppoe_username')->unique();
            $table->string('pppoe_password');
            $table->ipAddress('ip_address')->nullable();
            $table->string('queue_name')->nullable();
            $table->enum('status', ['active', 'soft_limited', 'suspended', 'terminated'])->default('active');
            $table->timestamp('last_ping_at')->nullable();
            $table->integer('ping_latency')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisionings');
    }
};
