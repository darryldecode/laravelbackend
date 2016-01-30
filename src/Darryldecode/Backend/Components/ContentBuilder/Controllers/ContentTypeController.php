<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/30/2015
 * Time: 8:35 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteContentTypeCommand;
use Darryldecode\Backend\Components\ContentBuilder\Commands\QueryContentTypeCommand;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class ContentTypeController extends BaseController {

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
     * displays content types page or content types data if ajax
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        if( $this->request->ajax() )
        {
            $result = $this->dispatch(new QueryContentTypeCommand(
                $this->request->get('type', null),
                false
            ));

            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            return view('contentBuilder::contentTypes');
        }
    }

    /**
     * handle post request create new content type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatch(new CreateContentTypeCommand(
            $this->request->get('type', null),
            $this->request->get('enableRevision', 'no'),
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete content type request
     *
     * @param $contentTypeId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($contentTypeId)
    {
        $result = $this->dispatch(new DeleteContentTypeCommand(
            $contentTypeId,
            false
        ));

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}