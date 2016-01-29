<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/31/2015
 * Time: 9:37 AM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class ContentTaxonomyController extends BaseController {

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
     * handle post request create new content taxonomy
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateContentTypeTaxonomyCommand',
            array(
                'taxonomy' => $this->request->get('taxonomy', null),
                'description' => $this->request->get('description', null),
                'parent' => $this->request->get('parent', null),
                'contentTypeId' => $this->request->get('contentTypeId', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete taxonomy request
     *
     * @param $taxonomyId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($taxonomyId)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyCommand',
            array(
                'taxonomyId' => $taxonomyId
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}