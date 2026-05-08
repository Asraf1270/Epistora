<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $blueprint) {
            $blueprint->string('comment_id')->primary();
            $blueprint->string('post_id');
            $blueprint->string('user_id');
            $blueprint->string('parent_id')->nullable(); // For nested replies
            $blueprint->text('text');
            $blueprint->timestamps();

            // Foreign Keys
            $blueprint->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->foreign('parent_id')->references('comment_id')->on('comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
