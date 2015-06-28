<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/26/2015
 * Time: 8:29 PM
 */

namespace Darryldecode\Backend\Components\Dashboard\Controllers;


use Darryldecode\Backend\Base\Controllers\BaseController;

class DashboardController extends BaseController {

    public function __construct()
    {
        parent::__construct();
        $this->middleware('backend.authenticated');
    }

    public function index()
    {
        return view('dashboard::dashboard');
    }
}