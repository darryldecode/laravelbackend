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
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Components\ContentBuilder\Models\ContentTypeFormGroup;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class UpdateFormGroupCommand extends Command implements SelfHandling {
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
     * @var
     */
    private $id;

    /**
     * @param null $id
     * @param null $name
     * @param null $formName
     * @param array $conditions
     * @param array $fields
     * @param null $contentTypeId
     * @param bool $disablePermissionChecking
     */
    public function __construct($id = null,
                                $name = null,
                                $formName = null,
                                $conditions = null,
                                $fields = null,
                                $contentTypeId = null, $disablePermissionChecking = false)
    {
        parent::__construct();
        $this->id = $id;
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
        if( ! is_null($this->fields) && (count($this->fields)==0) )
        {
            return new CommandResult(false, "Fields should have atleast 1 item", null, 400);
        }

        // fire event creating
        $dispatcher->fire('formGroup.updating', array($this->args));

        // begin create
        if( ! $formGroup = $contentTypeFormGroup->find($this->id) )
        {
            return new CommandResult(false, "Form group Not Found.", null, 400);
        }

        $formGroup->name = $this->name ? $this->name : $formGroup->name;
        $formGroup->form_name = $this->formName ? $this->formName : $formGroup->form_name;
        $formGroup->conditions = $this->conditions ? $this->conditions : $formGroup->conditions;
        $formGroup->fields = $this->fields ? $this->fields : $formGroup->fields;
        $formGroup->content_type_id = $this->contentTypeId ? $this->contentTypeId : $formGroup->content_type_id;

        if( ! $formGroup->save() )
        {
            return new CommandResult(false, "Failed to update form group.", null, 400);
        }

        // fire event creating
        $dispatcher->fire('formGroup.updated', array($formGroup));

        // all good
        return new CommandResult(true, "Form group successfully updated.", $formGroup, 200);
    }
}