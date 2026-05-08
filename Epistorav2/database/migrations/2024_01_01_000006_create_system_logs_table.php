<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $blueprint) {
            $blueprint->string('id')->primary();
            $blueprint->string('admin_id')->index();
            $blueprint->string('admin_name')->nullable();
            $blueprint->string('action')->index();
            $blueprint->text('details')->nullable();
            $blueprint->string('ip')->nullable();
            $blueprint->timestamp('legacy_timestamp')->nullable()->index();
            $blueprint->timestamps();

            // Foreign Keys
            $blueprint->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
