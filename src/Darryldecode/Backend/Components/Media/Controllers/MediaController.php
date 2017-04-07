<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/4/2015
 * Time: 7:57 PM
 */

namespace Darryldecode\Backend\Components\Media\Controllers;

use Darryldecode\Backend\Base\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory as Response;
use elFinder;
use elFinderConnector;

class MediaController extends BaseController {

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
    public function index()
    {
        $this->triggerBeforeBackendHook();
        return view('media::media');
    }

    /**
     * the elFinder handle
     */
    public function elFinder()
    {
        elFinder::$netDrivers['ftp'] = 'FTP';

        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = array(
            'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => storage_path('app/public'), // path to files (REQUIRED)
                    'URL'           => public_path('storage'), // URL to files (REQUIRED)
                    'uploadDeny'    => config('backend.backend.upload_rules.uploadDeny'),                // All Mimetypes not allowed to upload
                    'uploadAllow'   => config('backend.backend.upload_rules.uploadAllow'), // Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => config('backend.backend.upload_rules.uploadOrder'),      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }
}