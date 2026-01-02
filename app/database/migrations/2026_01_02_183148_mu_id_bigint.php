<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * May 2022 MangaUpdates started using a new ID range which no longer fits in
 * 32 bit integers. Fix by making MU ID columns BIGINT.
 */
class MuIdBigint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// operation not supported in laravel 4.2, use raw sql instead
		DB::statement('ALTER TABLE related_series MODIFY related_mu_id BIGINT UNSIGNED');
		DB::statement('ALTER TABLE series MODIFY mu_id BIGINT UNSIGNED');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE related_series MODIFY related_mu_id INT UNSIGNED');
		DB::statement('ALTER TABLE series MODIFY mu_id INT UNSIGNED');
	}

}
