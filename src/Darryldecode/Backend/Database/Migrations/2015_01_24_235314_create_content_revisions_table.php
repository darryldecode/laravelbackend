<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('content_revisions');
		Schema::create('content_revisions', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->text('old_content');
			$table->text('new_content');
			$table->unsignedInteger('content_id');
			$table->unsignedInteger('author_id');
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
		Schema::dropIfExists('content_revisions');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
