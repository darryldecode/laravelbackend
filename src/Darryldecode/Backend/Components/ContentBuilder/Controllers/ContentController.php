<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 5:23 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class ContentController extends BaseController {

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
     * get contents by type
     *
     * @param $contentType
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function getByType($contentType)
    {
        if( $this->request->ajax() )
        {
            $result = $this->dispatchFrom(
                'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentsCommand',
                $this->request,
                array(
                    'type' => $contentType,
                )
            );

            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            return view('contentBuilder::contents', array('contentType' => $contentType));
        }
    }

    /**
     * create new content
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand',
            $this->request
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * update content
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putUpdate($id)
    {
        $result = $this->dispatchFrom(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand',
            $this->request,
            array('id' => $id)
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * delete content
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentCommand',
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