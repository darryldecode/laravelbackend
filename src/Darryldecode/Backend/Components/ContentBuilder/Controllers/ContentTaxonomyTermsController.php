<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/1/2015
 * Time: 12:28 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class ContentTaxonomyTermsController extends BaseController {

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
     * handle get terms by taxonomy request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getByTaxonomyId()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\QueryTermsByTaxonomyCommand',
            array(
                'taxonomyId' => $this->request->get('taxonomyId', null)
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle create term request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCreate()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\CreateTypeTaxonomyTerm',
            array(
                'term' => $this->request->get('term', null),
                'slug' => $this->request->get('slug', null),
                'contentTypeTaxonomyId' => $this->request->get('contentTypeTaxonomyId', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * delete a term using ID
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\ContentBuilder\Commands\DeleteTaxonomyTermCommand',
            array(
                'taxonomyId' => $this->request->get('taxonomyId', null),
                'termId' => $id,
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }
}