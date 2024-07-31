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
    {
        Schema::create('sitemap_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sitemap_id');
            $table->string('page_title')->nullable();
            $table->string('page_url');
            $table->boolean('index_status')->default(false);
            $table->timestamps();

            // Foreign key constraint with cascading on delete
            $table->foreign('sitemap_id')->references('id')->on('sitemaps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemap_urls');
    }
};
