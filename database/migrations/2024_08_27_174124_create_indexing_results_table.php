<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexingResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indexing_results', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('url')->unique(); // URL to be indexed, unique to avoid duplicates
            $table->timestamp('index_date')->nullable(); // Date when the URL was indexed
            $table->unsignedBigInteger('sitemap_id'); // Foreign key to connect to sitemap table
            $table->timestamps(); // Created at and Updated at timestamps

            // Foreign key constraint to connect the sitemap_id to the sitemaps table's id
            $table->foreign('sitemap_id')->references('id')->on('sitemaps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indexing_results');
    }
}
