<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/4/2015
 * Time: 7:57 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class MediaManagerController extends BaseController {

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
     * index media manager
     *
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function ls()
    {
        if( $this->request->ajax() )
        {
            $result = $this->dispatchFromArray(
                'Darryldecode\Backend\Components\MediaManager\Commands\ListCommand',
                array(
                    'path' => $this->request->get('path', null),
                )
            );

            return $this->response->json(array(
                'data' => $result->getData()->toArray(),
                'message' => $result->getMessage()
            ), $result->getStatusCode());
        }
        else
        {
            $this->triggerBeforeBackendHook();

            return view('mediaManager::mediaManager');
        }
    }

    /**
     * handle post make directory command
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postMkDir()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\MediaManager\Commands\MakeDirectoryCommand',
            array(
                'path' => $this->request->get('path', null),
                'dirName' => $this->request->get('dirName', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle upload request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postUpload()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\MediaManager\Commands\UploadCommand',
            array(
                'path' => $this->request->get('path', null),
                'files' => $this->request->files,
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete file(s) request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\MediaManager\Commands\DeleteFileCommand',
            array(
                'paths' => $this->request->get('paths', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle delete file(s) request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDirectory()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\MediaManager\Commands\DeleteDirectoryCommand',
            array(
                'paths' => $this->request->get('paths', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle move file request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postMove()
    {
        $result = $this->dispatchFromArray(
            'Darryldecode\Backend\Components\MediaManager\Commands\MoveCommand',
            array(
                'path' => $this->request->get('path', null),
                'newPath' => $this->request->get('newPath', null),
            )
        );

        return $this->response->json(array(
            'data' => $result->getData()->toArray(),
            'message' => $result->getMessage()
        ), $result->getStatusCode());
    }

    /**
     * handle download file request
     */
    public function getDownload()
    {
        if( $this->request->get('token', null) != csrf_token() )
        {
            return $this->response->json(array(
                'data' => null,
                'message' => 'Invalid Token!'
            ), 400);
        }

        $filePath = $this->request->get('path', null);

        if( ! is_null($filePath) )
        {
            $fullPath = public_path().'/uploads/'.$filePath;

            return $this->response->download($fullPath);
        }
    }
}