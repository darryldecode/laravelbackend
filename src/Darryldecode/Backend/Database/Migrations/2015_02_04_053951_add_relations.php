<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// deletes all taxonomy under a deleted content type
		Schema::table('content_type_taxonomy', function(Blueprint $table)
		{
			$table->foreign('content_type_id')
				->references('id')
				->on('content_types')
				->onUpdate('cascade')
				->onDelete('cascade');
		});

		// deletes all terms that belongs to a deleted taxonomy
		Schema::table('content_type_taxonomy_terms', function(Blueprint $table)
		{
			$table->foreign('content_type_taxonomy_id')
				->references('id')
				->on('content_type_taxonomy')
				->onUpdate('cascade')
				->onDelete('cascade');
		});

		// delete all form group that belongs to a content type
		Schema::table('content_type_form_group', function(Blueprint $table)
		{
			$table->foreign('content_type_id')
				->references('id')
				->on('content_types')
				->onUpdate('cascade')
				->onDelete('cascade');
		});

		// if a content is deleted, delete its pivot entries for terms
		Schema::table('content_pivot_table', function(Blueprint $table)
		{
			$table->foreign('content_id')
				->references('id')
				->on('contents')
				->onUpdate('cascade')
				->onDelete('cascade');
		});
		// if a term is deleted, delete its pivot entries for contents
		Schema::table('content_pivot_table', function(Blueprint $table)
		{
			$table->foreign('content_type_taxonomy_term_id')
				->references('id')
				->on('content_type_taxonomy_terms')
				->onUpdate('cascade')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
