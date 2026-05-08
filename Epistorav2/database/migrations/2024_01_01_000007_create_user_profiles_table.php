<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('user_id')->unique();
            $blueprint->string('full_name')->nullable();
            $blueprint->string('email')->nullable()->index();
            $blueprint->text('bio')->nullable();
            $blueprint->string('writer_status')->nullable()->index();
            $blueprint->string('bg_color')->default('#ffffff');
            $blueprint->string('font_style')->default('sans-serif');
            $blueprint->string('font_size')->default('16px');
            $blueprint->json('history')->nullable(); // Array of Post IDs
            $blueprint->json('following')->nullable(); // Array of User IDs
            $blueprint->json('followers')->nullable(); // Array of User IDs
            $blueprint->timestamps();

            // Foreign Key
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
