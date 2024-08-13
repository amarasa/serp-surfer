<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceWorkersTable extends Migration
{
    public function up()
    {
        Schema::create('service_workers', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique(); // Service worker address
            $table->text('json_key'); // JSON key content
            $table->integer('used')->default(0); // Count of how many users are using this worker
            $table->timestamps();
        });

        // Add the service_worker_online column to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('service_worker_online')->default(false); // Indicates if the service worker is added to GSC
            $table->unsignedBigInteger('service_worker_id')->nullable(); // Foreign key to service_workers

            // Add foreign key constraint
            $table->foreign('service_worker_id')->references('id')->on('service_workers')->onDelete('set null');
        });
    }

    public function down()
    {
        // Rollback changes
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_worker_id']);
            $table->dropColumn('service_worker_online');
            $table->dropColumn('service_worker_id');
        });

        Schema::dropIfExists('service_workers');
    }
}
