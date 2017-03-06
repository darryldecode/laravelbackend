<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/2/2015
 * Time: 1:54 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteFormGroupCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateFormGroupCommand;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class ContentTypeFormGroupController extends BaseController {

    /**
     * @var
     */
    private $request;
    /**
     * @var
     */
    private $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct();
        $this->middleware('backend.authenticated');
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * show the backend page if not ajax or list all form groups
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        if( $this->request->ajax() )
        {
            $result = $this->dispatch(new QueryFormGroupCommand(
                $this->request->get('contentTypeId', null),
                $this->request->get('paginated', true),
                $this->request->get('perPage', 6),
                false
            ));

            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            return view('contentBuilder::customFields');
        }
    }

    /**
     * handle post create new form group and custom fields
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatch(new CreateFormGroupCommand(
            $this->request->get('name', null),
            $this->request->get('formName', null),
            $this->request->get('conditions', array()),
            $this->request->get('fields', array()),
            $this->request->get('contentTypeId', null),
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle put update request
     *
     * @param $formGroupId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putUpdate($formGroupId)
    {
        $result = $this->dispatch(new UpdateFormGroupCommand(
            $formGroupId,
            $this->request->get('name', null),
            $this->request->get('formName', null),
            $this->request->get('conditions', array()),
            $this->request->get('fields', array()),
            $this->request->get('contentTypeId', null),
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete form group request
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        $result = $this->dispatch(new DeleteFormGroupCommand(
            $id,
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}