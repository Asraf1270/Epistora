<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $blueprint) {
            // Legacy Post ID (e.g., 24A344-202512-U-...)
            $blueprint->string('post_id')->primary();
            $blueprint->string('title');
            $blueprint->string('author_id');
            $blueprint->longText('content');
            $blueprint->text('preview')->nullable();
            $blueprint->string('status')->default('published')->index();
            $blueprint->integer('views')->default(0);
            $blueprint->integer('reaction_count')->default(0);
            $blueprint->json('reactions_meta')->nullable(); // For detailed emoji counts
            $blueprint->date('legacy_date')->nullable()->index(); // Original YYYY-MM-DD
            $blueprint->timestamps();

            // Foreign Key
            $blueprint->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
