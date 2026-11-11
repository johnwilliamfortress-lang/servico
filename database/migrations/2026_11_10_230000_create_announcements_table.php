<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 120);
            $table->string('category', 80)->index();
            $table->text('description');
            $table->string('offer_info', 500);
            $table->string('contact');
            $table->string('image_path')->nullable();
            $table->string('management_token', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
