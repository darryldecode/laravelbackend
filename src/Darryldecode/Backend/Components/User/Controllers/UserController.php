<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 8:27 PM
 */

namespace Darryldecode\Backend\Components\User\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\QueryUsersCommand',
            array(
                'firstName' => $this->request->get('firstName', null),
                'lastName' => $this->request->get('lastName', null),
                'email' => $this->request->get('email', null),
                'groupId' => $this->request->get('groupId', null),
                'orderBy' => $this->request->get('orderBy', 'created_at'),
                'orderSort' => $this->request->get('orderSort', 'DESC'),
                'paginated' => $this->request->get('paginated', true),
                'perPage' => $this->request->get('perPage', 15),
                'with' => $this->request->get('with', array()),
            )
        );

        if($this->request->ajax())
        {
            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\CreateUserCommand',
            array(
                'firstName' => $this->request->get('firstName', null),
                'lastName' => $this->request->get('lastName', null),
                'email' => $this->request->get('email', null),
                'password' => $this->request->get('password', null),
                'permissions' => $this->request->get('permissions', array()),
                'groups' => $this->request->get('groups', array()),
            )
        );

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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\UpdateUserCommand',
            array(
                'id' => $userId,
                'firstName' => $this->request->get('firstName', null),
                'lastName' => $this->request->get('lastName', null),
                'email' => $this->request->get('email', null),
                'password' => $this->request->get('password', null),
                'permissions' => $this->request->get('permissions', null),
                'groups' => $this->request->get('groups', null),
            )
        );

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
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\User\Commands\DeleteUserCommand',
            array(
                'id' => $userId,
            )
        );

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