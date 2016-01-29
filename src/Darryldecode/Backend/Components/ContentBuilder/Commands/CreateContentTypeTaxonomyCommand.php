<?php namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeTaxonomy;
use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Validation\Factory as Validator;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Illuminate\Contracts\Events\Dispatcher;

class CreateContentTypeTaxonomyCommand extends Command implements SelfHandling {
	/**
	 * @var
	 */
	private $taxonomy;
	/**
	 * @var
	 */
	private $contentTypeId;
	/**
	 * @var
	 */
	private $description;

    /**
     * Create a new command instance.
     *
     * @param string $taxonomy
     * @param string $description
     * @param int $contentTypeId
     * @param bool $disablePermissionChecking
     */
	public function __construct($taxonomy = null, $description = null, $contentTypeId = null, $disablePermissionChecking = false)
	{
		parent::__construct();
		$this->taxonomy = $taxonomy;
		$this->contentTypeId = $contentTypeId;
		$this->description = $description;
		$this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
	}

	/**
	 * Execute the command.
	 *
	 * @param ContentType $contentType
	 * @param Validator $validator
	 * @param Dispatcher $dispatcher
	 * @return CommandResult
	 */
	public function handle(ContentType $contentType, Validator $validator, Dispatcher $dispatcher)
	{
		// validate authorization
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['contentBuilder.manage']) )
            {
                return new CommandResult(false, CommandResult::$responseForbiddenMessage, null, 403);
            }
        }

		// validate data
		$validationResult = $validator->make(array(
			'taxonomy' => $this->taxonomy,
			'content_type_id' => $this->contentTypeId,
		), ContentTypeTaxonomy::$rules);

		if( $validationResult->fails() )
		{
			return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
		}

		// prepare the data to be stored
		$taxonomyToBeCreated = array(
			'taxonomy' => $this->taxonomy,
			'description' => $this->description,
		);

		// fire creating event
		$dispatcher->fire('contentTypeTaxonomy.creating', array($taxonomyToBeCreated));

		// store
		try {
			$contentType = $contentType->findOrFail($this->contentTypeId);

			$createdContentTypeTaxonomy = $contentType->taxonomies()->create($taxonomyToBeCreated);
		} catch (\Exception $e)
		{
			return new CommandResult(false, "Invalid Content Type.", null, 400);
		}

		// fire creating event
		$dispatcher->fire('contentTypeTaxonomy.created', array($createdContentTypeTaxonomy));

		// return
		return new CommandResult(true, "Content type taxonomy successfully created.", $createdContentTypeTaxonomy, 201);
	}

}
