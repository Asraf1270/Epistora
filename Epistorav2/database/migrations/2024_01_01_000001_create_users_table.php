<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $blueprint) {
            // Using string for ID to preserve legacy compatibility (U-...)
            $blueprint->string('id')->primary();
            $blueprint->string('name');
            $blueprint->string('email')->unique();
            $blueprint->string('password');
            $blueprint->enum('role', ['admin', 'writer', 'v_writer', 'user'])->default('user');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
