<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('index_queue', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('url'); // URL of the page being indexed
            $table->foreignId('sitemap_id')->constrained()->onDelete('cascade'); // Foreign key referencing the sitemap
            $table->dateTime('requested_index_date')->nullable(); // Date when the index was requested
            $table->dateTime('last_scanned_date')->nullable(); // Date when it was last scanned
            $table->integer('submission_count')->default(1); // This is how many times we submit to get indexed
            $table->timestamps(); // Laravel's created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('index_queue');
    }
}
