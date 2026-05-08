<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $blueprint) {
            $blueprint->string('app_id')->primary();
            $blueprint->string('user_id');
            $blueprint->string('status')->default('pending')->index();
            $blueprint->unsignedBigInteger('legacy_timestamp')->nullable()->index();
            $blueprint->string('date_human')->nullable();
            $blueprint->string('full_name');
            $blueprint->string('father_name')->nullable();
            $blueprint->string('email')->nullable()->index();
            $blueprint->string('phone')->nullable();
            $blueprint->date('dob')->nullable();
            $blueprint->text('address')->nullable();
            $blueprint->text('sample_text')->nullable();
            $blueprint->json('portfolio')->nullable();
            $blueprint->timestamps();

            // Foreign Keys
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
