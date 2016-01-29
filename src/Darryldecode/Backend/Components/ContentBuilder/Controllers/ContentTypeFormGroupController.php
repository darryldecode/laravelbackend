<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/2/2015
 * Time: 1:54 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
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
            $result = $this->dispatchFromArray(
                'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryFormGroupCommand',
                array(
                    'paginated' => $this->request->get('paginated', true),
                    'perPage' => $this->request->get('perPage', 6),
                    'contentTypeId' => $this->request->get('contentTypeId', null),
                )
            );

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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateFormGroupCommand',
            array(
                'name' => $this->request->get('name', null),
                'formName' => $this->request->get('formName', null),
                'conditions' => $this->request->get('conditions', array()),
                'fields' => $this->request->get('fields', array()),
                'contentTypeId' => $this->request->get('contentTypeId', null),
            )
        );

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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateFormGroupCommand',
            array(
                'id' => $formGroupId,
                'name' => $this->request->get('name', null),
                'formName' => $this->request->get('formName', null),
                'conditions' => $this->request->get('conditions', array()),
                'fields' => $this->request->get('fields', array()),
                'contentTypeId' => $this->request->get('contentTypeId', null),
            )
        );

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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteFormGroupCommand',
            array(
                'id' => $id,
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}