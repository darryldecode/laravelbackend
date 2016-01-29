<?php namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Events\Dispatcher;

class CreateContentTypeCommand extends Command implements SelfHandling {
	/**
	 * @var
	 */
	private $type;
	/**
	 * @var
	 */
	private $enableRevision;

    /**
     * Create a new command instance.
     *
     * @param string $type
     * @param string $enableRevision
     * @param bool $disablePermissionChecking
     */
	public function __construct($type, $enableRevision = 'no', $disablePermissionChecking = false)
	{
		parent::__construct();
		$this->type = $type;
		$this->enableRevision = $enableRevision;
        $this->disablePermissionChecking = $disablePermissionChecking;
	}

	/**
	 * Execute the command.
	 *
	 * @param Factory $validator
	 * @param ContentType $contentType
	 * @param Dispatcher $dispatcher
	 * @return CommandResult
	 */
	public function handle(Factory $validator, ContentType $contentType, Dispatcher $dispatcher)
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
			'type' => $this->type,
		), ContentType::$rules);

		if( $validationResult->fails() )
		{
			return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
		}

		// prepare data to be created
		$contentTypeToBeCreated = array(
			'type' => $this->type,
			'enable_revisions' => ($this->enableRevision == 'no') ? false : true,
		);

		// fire content type creating event
		$dispatcher->fire('contentType.creating', array($contentTypeToBeCreated));

		// store
		$createdContentType = $contentType->create($contentTypeToBeCreated);

		// fire content type created event
		$dispatcher->fire('contentType.created', array($createdContentType));

		// return
		return new CommandResult(true, "Content type successfully created.", $createdContentType, 201);
	}

}
