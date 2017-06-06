<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mu_id')->unsigned();
            $table->string('name');
            $table->integer('year')->unsigned();
            $table->text('description')->nullable();
            $table->string('origin_status')->nullable();
            $table->string('scan_status')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('needs_update')->default(0);
            $table->timestamps();
        });

        Schema::table('path_records', function (Blueprint $table) {
            $table->integer('series_id')->unsigned()->nullable();
            $table->foreign('series_id')->references('id')->on('series');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('path_records', function (Blueprint $table) {
            $table->dropForeign('path_records_series_id_foreign');
            $table->dropColumn('series_id');
        });

        Schema::drop('series');
    }
}
