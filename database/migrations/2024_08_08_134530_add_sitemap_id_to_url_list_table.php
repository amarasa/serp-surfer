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
        Schema::table('url_list', function (Blueprint $table) {
            $table->unsignedBigInteger('sitemap_id')->after('id')->nullable(); // Add sitemap_id column
            $table->foreign('sitemap_id')->references('id')->on('sitemaps')->onDelete('cascade'); // Add foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_list', function (Blueprint $table) {
            $table->dropForeign(['sitemap_id']); // Drop the foreign key constraint
            $table->dropColumn('sitemap_id'); // Drop the sitemap_id column
        });
    }
};
