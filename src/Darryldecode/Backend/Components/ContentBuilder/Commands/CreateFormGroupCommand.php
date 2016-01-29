<?php
/**
 * @todo create tests for events
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/3/2015
 * Time: 8:22 AM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Commands;

use Darryldecode\Backend\Base\Commands\Command;
use Darryldecode\Backend\Base\Commands\CommandResult;
use Darryldecode\Backend\Components\ContentBuilder\Models\Content;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Darryldecode\Backend\Utility\Helpers;

class CreateFormGroupCommand extends Command implements SelfHandling {
    /**
     * @var null
     */
    private $name;
    /**
     * @var null
     */
    private $formName;
    /**
     * @var array
     */
    private $conditions;
    /**
     * @var array
     */
    private $fields;
    /**
     * @var null
     */
    private $contentTypeId;

    /**
     * @param null $name
     * @param null $formName
     * @param array $conditions
     * @param array $fields
     * @param null $contentTypeId
     * @param bool $disablePermissionChecking
     */
    public function __construct($name = null,
                                $formName = null,
                                $conditions = array(),
                                $fields = array(),
                                $contentTypeId = null,
                                $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->formName = $formName;
        $this->conditions = $conditions;
        $this->fields = $fields;
        $this->contentTypeId = $contentTypeId;
        $this->args = get_defined_vars();
        $this->disablePermissionChecking = $disablePermissionChecking;
    }

    /**
     * @param Factory $validator
     * @param Dispatcher $dispatcher
     * @param ContentType $contentType
     * @param ContentTypeFormGroup $contentTypeFormGroup
     * @return CommandResult
     */
    public function handle(Factory $validator, Dispatcher $dispatcher, ContentType $contentType, ContentTypeFormGroup $contentTypeFormGroup)
    {
        // check if user has permission
        if( ! $this->disablePermissionChecking )
        {
            if( ! $this->user->hasAnyPermission(['contentBuilder.manage']) )
            {
                return new CommandResult(false, "Not enough permission.", null, 403);
            }
        }

        // validate data
        $validationResult = $validator->make(array(
            'name' => $this->name,
            'form_name' => $this->formName,
            'fields' => $this->fields,
            'content_type_id' => $this->contentTypeId,
        ), ContentTypeFormGroup::$rules);

        if( $validationResult->fails() )
        {
            return new CommandResult(false, $validationResult->getMessageBag()->first(), null, 400);
        }

        // fire event creating
        $dispatcher->fire('formGroup.creating', array($this->args));

        // begin create
        if( ! $cType = $contentType->find($this->contentTypeId) )
        {
            return new CommandResult(false, "Content Type Not Found.", null, 400);
        }

        $createdFormGroup = $cType->formGroups()->create(array(
            'name' => $this->name,
            'form_name' => $this->formName,
            'conditions' => $this->conditions,
            'fields' => $this->fields,
            'content_type_id' => $this->contentTypeId,
        ));

        // fire event creating
        $dispatcher->fire('formGroup.created', array($createdFormGroup));

        // all good
        return new CommandResult(true, "Form group successfully created.", $createdFormGroup, 201);
    }
}