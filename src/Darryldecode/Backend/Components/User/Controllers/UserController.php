<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 8:27 PM
 */

namespace Darryldecode\Backend\Components\User\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Darryldecode\Backend\Components\User\Commands\CreateUserCommand;
use Darryldecode\Backend\Components\User\Commands\DeleteUserCommand;
use Darryldecode\Backend\Components\User\Commands\QueryUsersCommand;
use Darryldecode\Backend\Components\User\Commands\UpdateUserCommand;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class UserController extends BaseController {

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
     * lists users
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $result = $this->dispatch(new QueryUsersCommand(
            null,
            $this->request->get('firstName', null),
            $this->request->get('lastName', null),
            $this->request->get('email', null),
            $this->request->get('groupId', null),
            $this->request->get('with', array()),
            $this->request->get('orderBy', 'created_at'),
            $this->request->get('orderSort', 'DESC'),
            $this->request->get('paginated', true),
            $this->request->get('perPage', 15),
            false,
            null
        ));

        if($this->request->ajax())
        {
            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            $this->triggerBeforeBackendHook();

            return view('userManager::users', compact('result'));
        }
    }

    /**
     * handle post create user request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatch(new CreateUserCommand(
            $this->request->get('firstName', null),
            $this->request->get('lastName', null),
            $this->request->get('email', null),
            $this->request->get('password', null),
            $this->request->get('permissions', array()),
            $this->request->get('groups', array()),
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle put update user request
     *
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putUpdate($userId)
    {
        $result = $this->dispatch(new UpdateUserCommand(
            $userId,
            $this->request->get('firstName', null),
            $this->request->get('lastName', null),
            $this->request->get('email', null),
            $this->request->get('password', null),
            $this->request->get('permissions', null),
            $this->request->get('groups', null),
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete user request
     *
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($userId)
    {
        $result = $this->dispatch(new DeleteUserCommand(
            $userId,
            false
        ));

        return $this->response->json(array(
            'data' => null,
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * get available permissions
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAvailablePermissions()
    {
        $backend = app('backend');

        $results = $backend->getAvailablePermissions();

        return $this->response->json(array(
            'data' => $results,
            'message' => 'Available permissions query successful.'
        ), 200);
    }
}