<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket__histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('placed_ticket_id');
            $table->foreign('placed_ticket_id')->references('id')->on('placed__tickets');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->unsignedBigInteger('executor_id');
            $table->foreign('executor_id')->references('id')->on('executors');
            $table->integer('rating')->nullable();
            $table->string('description');
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
        Schema::dropIfExists('ticket__histories');
    }
}
