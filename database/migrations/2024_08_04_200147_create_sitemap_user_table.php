<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    { {
            Schema::create('sitemap_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sitemap_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('sitemap_id')->references('id')->on('sitemaps')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->unique(['sitemap_id', 'user_id']); // Ensure a unique combination of sitemap and user
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemap_user');
    }
};
