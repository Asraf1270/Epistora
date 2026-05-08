<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $blueprint) {
            $blueprint->string('id')->primary();
            $blueprint->string('user_id')->index(); // Recipient
            $blueprint->string('type')->index(); // comment, reaction, etc.
            $blueprint->string('from_name')->nullable(); // Legacy denormalized name
            $blueprint->string('post_id')->nullable();
            $blueprint->boolean('is_read')->default(false)->index();
            $blueprint->timestamp('legacy_timestamp')->nullable()->index();
            $blueprint->string('date_human')->nullable();
            $blueprint->timestamps();

            // Foreign Keys
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
