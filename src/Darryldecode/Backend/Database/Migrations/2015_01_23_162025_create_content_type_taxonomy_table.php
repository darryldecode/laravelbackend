<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentTypeTaxonomyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('content_type_taxonomy');
		Schema::create('content_type_taxonomy', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('taxonomy')->index();
			$table->text('description');
			$table->unsignedInteger('content_type_id');
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
		Schema::dropIfExists('content_type_taxonomy');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
