<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePathRecordsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('path_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path', 4096);
            $table->char('path_hash', 40);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('path_records')->onUpdate('cascade')->onDelete('set null');
            $table->tinyInteger('directory')->default(0);
            $table->bigInteger('size')->unsigned();
            $table->dateTime('created');
            $table->dateTime('modified');
            $table->tinyInteger('locked')->default(0);
            $table->text('comment')->nullable();
            $table->integer('downloads')->unsigned()->default(0);
            $table->dateTime('downloaded_at')->nullable();
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
        Schema::drop('path_records');
    }
}
