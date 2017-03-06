<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/4/2015
 * Time: 7:57 PM
 */

namespace Darryldecode\Backend\Components\MediaManager\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Darryldecode\Backend\Components\MediaManager\Commands\DeleteDirectoryCommand;
use Darryldecode\Backend\Components\MediaManager\Commands\DeleteFileCommand;
use Darryldecode\Backend\Components\MediaManager\Commands\ListCommand;
use Darryldecode\Backend\Components\MediaManager\Commands\MakeDirectoryCommand;
use Darryldecode\Backend\Components\MediaManager\Commands\MoveCommand;
use Darryldecode\Backend\Components\MediaManager\Commands\UploadCommand;
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
            $result = $this->dispatch(new ListCommand(
                $this->request->get('path', null),
                false
            ));

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
        $result = $this->dispatch(new MakeDirectoryCommand(
            $this->request->get('path', null),
            $this->request->get('dirName', null),
            false
        ));

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
        $result = $this->dispatch(new UploadCommand(
            $this->request->files,
            $this->request->get('path', null),
            false
        ));

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
        $result = $this->dispatch(new DeleteFileCommand(
            $this->request->get('paths', null),
            false
        ));

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
        $result = $this->dispatch(new DeleteDirectoryCommand(
            $this->request->get('paths', null),
            false
        ));

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
        $result = $this->dispatch(new MoveCommand(
            $this->request->get('path', null),
            $this->request->get('newPath', null),
            false
        ));

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
            $fullPath = config('filesystems.disks.public.root').'/'.$filePath;

            return $this->response->download($fullPath);
        }
    }
}