<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelatedSeriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('related_series', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('series_id')->unsigned();
            $table->foreign('series_id')->references('id')->on('series');
            $table->integer('related_mu_id')->unsigned();
            $table->enum('type', ['Adapted From', 'Main Story', 'Sequel', 'Alternate Story', 'Side Story', 'Spin-Off', 'Prequel']);
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
        Schema::drop('related_series');
    }
}
