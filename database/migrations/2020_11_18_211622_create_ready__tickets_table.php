<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadyTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ready__tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->unsignedBigInteger('executor_id');
            $table->foreign('executor_id')->references('id')->on('executors');
            $table->unsignedBigInteger('placed_ticket_id');
            $table->foreign('placed_ticket_id')->references('id')->on('placed__tickets');
            $table->unsignedBigInteger('chosen_id');
            $table->foreign('chosen_id')->references('id')->on('executors__on__tickets');
            $table->boolean('active')->default(false);
            $table->string('video_path')->nullable();
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ready__tickets');
    }
}
