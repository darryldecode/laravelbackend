<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentTypeTaxonomyTermsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('content_type_taxonomy_terms');
		Schema::create('content_type_taxonomy_terms', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('term')->index();
			$table->string('slug')->unique();
			$table->unsignedInteger('content_type_taxonomy_id');
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
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		Schema::dropIfExists('content_type_taxonomy_terms');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
