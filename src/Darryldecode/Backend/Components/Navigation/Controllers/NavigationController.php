<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/5/2015
 * Time: 10:08 PM
 */

namespace Darryldecode\Backend\Components\Navigation\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class NavigationController extends BaseController {

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
     * list navs, this is the master navigation (pre built navigation from components)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\ListNavigationCommand',
            array()
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * displays the navigation builder
     *
     * @return \Illuminate\View\View
     */
    public function getNavBuilderDisplay()
    {
        if( $this->request->ajax() )
        {
            $result = $this->dispatchFromArray(
                'Darryldecode\Backend\Components\Navigation\Commands\ListCustomNavigationCommand',
                array()
            );

            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            $this->triggerBeforeBackendHook();

            return view('navigationBuilder::navigation-builder');
        }
    }

    /**
     * handle post create navigation request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatchFrom(
            'Darryldecode\Backend\Components\Navigation\Commands\CreateNavigationCommand',
            $this->request
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle put update navigation request
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function putUpdate($id)
    {
        $result = $this->dispatchFrom(
            'Darryldecode\Backend\Components\Navigation\Commands\UpdateNavigationCommand',
            $this->request,
            array(
                'id' => $id
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete navigation request
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\Navigation\Commands\DeleteCustomNavigationCommand',
            array(
                'id' => $id
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}