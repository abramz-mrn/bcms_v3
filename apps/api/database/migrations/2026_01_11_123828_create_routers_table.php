<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->integer('api_port')->default(8729);
            $table->integer('ssh_port')->default(22);
            $table->string('username');
            $table->string('password');
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->json('config_backup')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
