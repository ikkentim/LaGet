<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldsToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nuget_packages', function(Blueprint $table)
        {
            $table->longText('description')->nullable()->change();
            $table->longText('summary')->nullable()->change();
            $table->longText('release_notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nuget_packages', function(Blueprint $table)
        {
            $table->string('description')->nullable()->change();
            $table->string('summary')->nullable()->change();
            $table->string('release_notes')->nullable()->change();
        });
        //
    }
}
