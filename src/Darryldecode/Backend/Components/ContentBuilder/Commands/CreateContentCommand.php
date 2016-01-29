<?php namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;

use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Darryldecode\Backend\Utility\Helpers;
use Illuminate\Contracts\Config\Repository;

class CreateContentCommand extends Command implements SelfHandling {
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $body;
	/**
	 * @var string
	 */
	private $slug;
	/**
	 * @var int
	 */
	private $authorId;
	/**
	 * @var int
	 */
	private $contentTypeId;
	/**
	 * @var array|null
	 */
	private $permissionRequirements;
	/**
	 * @var array
	 */
	private $miscData;
	/**
	 * @var
	 */
	private $status;
	/**
	 * @var array
	 */
	private $metaData;
	/**
	 * @var array
	 */
	private $taxonomies;

    /**
     * Create a new command instance.
     * @param string $title
     * @param string $body
     * @param string $slug
     * @param $status
     * @param int $authorId
     * @param int $contentTypeId
     * @param null|array $permissionRequirements
     * @param array $taxonomies
     * @param array $miscData
     * @param array $metaData
     * @param bool $disablePermissionChecking
     */
	public function __construct($title, $body, $slug, $status, $authorId, $contentTypeId, $permissionRequirements = null, $taxonomies = array(), $miscData = array(), $metaData = array(), $disablePermissionChecking = false)
	{
		parent::__construct();
		$this->title = $title;
		$this->body = $body;
		$this->slug = $slug;
		$this->authorId = $authorId;
		$this->contentTypeId = $contentTypeId;
		$this->permissionRequirements = $permissionRequirements;
		$this->miscData = $miscData;
		$this->status = $status;
		$this->metaData = $metaData;
		$this->taxonomies = $taxonomies;
        $this->disablePermissionChecking = $disablePermissionChecking;
	}

    /**
     * Execute the command.
     *
     * @param Content $content
     * @param Factory $validator
     * @param ContentType $contentType
     * @param Dispatcher $dispatcher
     * @param Repository $config
     * @return CommandResult
     */
	public function handle(Content $content, Factory $validator, ContentType $contentType, Dispatcher $dispatcher, Repository $config)
	{
        $content = $this->createContentModel($content, $config);

		// get content available permissions
		try {
			$cType = $contentType->findOrFail($this->contentTypeId);
			$cTypeManage = $cType->type.'.manage';
		} catch(\Exception $e)
		{
			return new CommandResult(false, "Invalid Content Type.", null, 400);
		}

		// check if user has permission
		if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission([$cTypeManage]) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

		// validate data
		$validationResult = $validator->make(array(
			'title' => $this->title,
			'body' => $this->body,
			'slug' => $this->slug,
			'author_id' => $this->authorId,
			'content_type_id' => $this->contentTypeId,
		), $content::$rules);

		if( $validationResult->fails() )
		{
			return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
		}

		// prepare data to be store
		$contentToBeCreated = array(
			'title' => $this->title,
			'body' => $this->body,
			'slug' => $this->slug,
			'status' => Helpers::issetAndHasValueOrAssignDefault($this->status, Content::CONTENT_PUBLISHED),
			'author_id' => $this->authorId,
			'content_type_id' => $this->contentTypeId,
			'meta' => $this->metaData,
			'misc_data' => $this->miscData,
			'taxonomies' => $this->taxonomies,
		);

		// fire event creating
		$dispatcher->fire($cType->type.'.creating', array($contentToBeCreated));

		$createdContent = $content->create($contentToBeCreated);

		// taxonomy
		foreach($contentToBeCreated['taxonomies'] as $termId => $value)
		{
			if( $value == true )
			{
				$createdContent->terms()->attach(array(
					'content_type_taxonomy_term_id' => $termId
				));
			}
		}

		// meta
		foreach($contentToBeCreated['meta'] as $formGroup => $formGroupData)
		{
			foreach($formGroupData as $metaKey => $metaValue)
			{
				$createdContent->metaData()->create(array(
					'key' => $metaKey,
					'value' => $metaValue,
					'form_group_name' => $formGroup
				));
			}
		}

		// fire event created
		$dispatcher->fire($cType->type.'.created', array($createdContent));

		// return response
		return new CommandResult(true, "Content successfully created.", $createdContent, 201);
	}

    /**
     * @param $content \Darryldecode\Backend\Components\ContentBuilder\Models\Content
     * @param $config \Illuminate\Contracts\Config\Repository
     * @return mixed
     */
    protected function createContentModel($content, $config)
    {
        if( ! $contentModelUsed = $config->get('backend.backend.content_model') )
        {
            return $content;
        };

        $contentModelUsed = new $contentModelUsed();

        if( $contentModelUsed instanceof Content )
        {
            return $contentModelUsed;
        }

        return $content;
    }
}
