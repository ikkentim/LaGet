<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdFieldToNugetPackageRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nuget_package_revisions', function(Blueprint $table)
		{
			$table->integer('user_id')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nuget_package_revisions', function(Blueprint $table)
		{
			$table->dropColumn('user_id');
		});
	}

}
