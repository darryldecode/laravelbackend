<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 5:23 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentsCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\UpdateContentCommand;
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
            $result = $this->dispatch(new QueryContentsCommand(
                $contentType,
                $this->request->get('status','any'),
                $this->request->get('authorId',null),
                $this->request->get('terms',array()),
                $this->request->get('meta',array()),
                $this->request->get('paginated',true),
                $this->request->get('perPage',8),
                $this->request->get('sortBy','created_at'),
                $this->request->get('sortOrder','DESC'),
                $this->request->get('with',array()),
                $this->request->get('disablePermissionChecking',false),
                $this->request->get('startDate',null),
                $this->request->get('endDate',null),
                $this->request->get('queryHook',null)
            ));

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
        $result = $this->dispatch(new CreateContentCommand(
            $this->request->get('title'),
            $this->request->get('body'),
            $this->request->get('slug'),
            $this->request->get('status'),
            $this->request->get('authorId'),
            $this->request->get('contentTypeId'),
            $this->request->get('permissionRequirements',null),
            $this->request->get('taxonomies'),
            $this->request->get('miscData'),
            false
        ));

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
        $result = $this->dispatch(new UpdateContentCommand(
            $id,
            $this->request->get('title',null),
            $this->request->get('body',null),
            $this->request->get('slug',null),
            $this->request->get('status',null),
            $this->request->get('authorId',null),
            $this->request->get('contentTypeId',null),
            $this->request->get('permissionRequirements',null),
            $this->request->get('taxonomies',null),
            $this->request->get('miscData',null),
            $this->request->get('metaData',null),
            false
        ));

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
        $result = $this->dispatch(new DeleteContentCommand(
            $id,
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}