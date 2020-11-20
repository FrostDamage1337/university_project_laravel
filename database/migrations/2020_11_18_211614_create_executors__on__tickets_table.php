<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExecutorsOnTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('executors__on__tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('placed_ticket_id');
            $table->foreign('placed_ticket_id')->references('id')->on('placed__tickets');
            $table->unsignedBigInteger('executor_id');
            $table->foreign('executor_id')->references('id')->on('executors');
            $table->integer('price');
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
        Schema::dropIfExists('executors__on__tickets');
    }
}
