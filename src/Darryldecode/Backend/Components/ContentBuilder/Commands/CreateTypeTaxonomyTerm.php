<?php namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomyTerm;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Validation\Factory as Validator;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Events\Dispatcher;

class CreateTypeTaxonomyTerm extends Command implements SelfHandling {
	/**
	 * @var
	 */
	private $term;
	/**
	 * @var
	 */
	private $contentTypeTaxonomyId;
	/**
	 * @var
	 */
	private $slug;

    /**
     * Create a new command instance.
     *
     * @param string $term
     * @param $slug
     * @param int $contentTypeTaxonomyId
     * @param bool $disablePermissionChecking
     */
	public function __construct($term = null, $slug = null, $contentTypeTaxonomyId = null, $disablePermissionChecking = false)
	{
		parent::__construct();
		$this->term = $term;
		$this->contentTypeTaxonomyId = $contentTypeTaxonomyId;
		$this->slug = $slug;
        $this->disablePermissionChecking = $disablePermissionChecking;
	}

	/**
	 * Execute the command.
	 *
	 * @param ContentType $contentType
	 * @param ContentTypeTaxonomy $contentTypeTaxonomy
	 * @param Validator $validator
	 * @param Dispatcher $dispatcher
	 * @return CommandResult
	 */
	public function handle(ContentType $contentType, ContentTypeTaxonomy $contentTypeTaxonomy, Validator $validator, Dispatcher $dispatcher)
	{
		// in order to determine what permissions are needed to create
		// a taxonomy terms, we will get first what taxonomy the term is for
		// after we can get the taxonomy, we will then get what type the taxonomy
		// belong so we can verify if the user has permission for that type
		try {
			$taxonomy = $contentTypeTaxonomy->findOrFail($this->contentTypeTaxonomyId);
		} catch (\Exception $e){
			return new CommandResult(false, "Invalid Taxonomy.", null, 400);
		}

		try {
			$type = $contentType->findOrFail($taxonomy->content_type_id);
		} catch (\Exception $e){
			return new CommandResult(false, "Invalid Content Type.", null, 400);
		}

		// build the permissions needed
		$canManageOnThisType = $type->type.'.manage';

		// check if user has permission
		if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$canManageOnThisType]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

		// validate data
		$validationResult = $validator->make(array(
			'term' => $this->term,
			'slug' => $this->slug,
			'content_type_taxonomy_id' => $this->contentTypeTaxonomyId,
		), ContentTypeTaxonomyTerm::$rules);

		if( $validationResult->fails() )
		{
			return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
		}

		// prepare term to be created
		$termToBeCreated = array(
			'term' => $this->term,
			'slug' => $this->slug,
			'content_type_taxonomy_id' => $this->contentTypeTaxonomyId,
		);

		// fire creating event
		$dispatcher->fire('contentTypeTaxonomyTerm.creating', array($termToBeCreated));

		// store
		$createdTerm = $taxonomy->terms()->create($termToBeCreated);

		// fire creating event
		$dispatcher->fire('contentTypeTaxonomyTerm.created', array($createdTerm));

		// all good
		return new CommandResult(true, "Taxonomy term successfully created.", $createdTerm, 201);
	}

}
