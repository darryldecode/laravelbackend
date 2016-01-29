<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/28/2015
 * Time: 8:24 AM
 */

namespace Darryldecode\Backend\Components\User\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class GroupController extends BaseController {

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
     * list groups
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $results = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryGroupsCommand',
            array(
                'name' => $this->request->get('name',null),
                'with' => $this->request->get('with',array()),
                'paginate' => $this->request->get('paginate',null),
                'perPage' => $this->request->get('perPage',15),
            )
        );

        if( $this->request->ajax() )
        {
            return $this->response->json(array(
                'data' => $results->getData()->toArray(),
                'message' => $results->getMessage()
            ), $results->getStatusCode());
        }
        else
        {
            $this->triggerBeforeBackendHook();

            return view('userManager::groups', compact('results'));
        }
    }

    /**
     * handle create group post request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatchFrom(
            'Darryldecode\Backend\Components\User\Commands\CreateGroupCommand',
            $this->request
        );

        return $this->response->json(array(
            'data' => $result->getData(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle update group put request
     *
     * @param $groupId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putUpdate($groupId)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateGroupCommand',
            array(
                'id' => $groupId,
                'name' => $this->request->get('name', null),
                'permissions' => $this->request->get('permissions', array()),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete request
     *
     * @param $groupId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($groupId)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\DeleteGroupCommand',
            array(
                'id' => $groupId,
            )
        );

        return $this->response->json(array(
            'data' => $result->getData(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}