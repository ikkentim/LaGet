<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDownloadCountToNugetPackageRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nuget_package_revisions', function(Blueprint $table)
		{
			$table->integer('download_count')->default(0);
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
			$table->dropColumn('download_count');
		});
	}

}
