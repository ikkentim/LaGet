<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNugetPackageRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nuget_package_revisions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('path');
			$table->string('package_id');
			$table->string('version');
			$table->boolean('is_prerelease');
			$table->string('title');
			$table->string('authors');
			$table->string('owners');
			$table->string('icon_url');
			$table->string('license_url');
			$table->string('project_url');
			$table->boolean('require_license_acceptance');
			$table->boolean('development_dependency');
			$table->string('description');
			$table->string('summary');
			$table->string('release_notes');
			$table->string('dependencies');
			$table->string('hash');
			$table->string('hash_algorithm');
			$table->integer('size');
			$table->string('copyright');
			$table->string('tags');
			$table->boolean('is_absolute_latest_version');
			$table->boolean('is_latest_version');
			$table->boolean('is_listed');
			$table->integer('version_download_count');
			$table->string('min_client_version');
			$table->string('language');
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
		Schema::drop('nuget_package_revisions');
	}

}
