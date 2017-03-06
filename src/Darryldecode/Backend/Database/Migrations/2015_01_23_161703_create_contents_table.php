<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('contents');
		Schema::create('contents', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('title')->index();
			$table->text('body');
			$table->string('slug');
			$table->string('status');
			$table->text('permission_requirements')->nullable();
			$table->unsignedInteger('author_id');
			$table->unsignedInteger('content_type_id');
			$table->text('misc_data')->nullable();
			$table->softDeletes();
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
		Schema::dropIfExists('contents');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
