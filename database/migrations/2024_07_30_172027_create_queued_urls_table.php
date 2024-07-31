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
        Schema::create('queued_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sitemap_id');
            $table->string('url');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('sitemap_id')
                ->references('id')
                ->on('sitemaps')
                ->onDelete('cascade'); // Cascade delete

            // Indexes
            $table->index('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queued_urls');
    }
};
