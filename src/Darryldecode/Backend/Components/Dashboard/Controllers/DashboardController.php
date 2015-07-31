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

    /**
     * dashboard constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('backend.authenticated');
    }

    /**
     * Display the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $widgets = app('backend')->getActiveWidgets();

        // the greater the value, the higher the priority
        usort($widgets, function($a, $b) {

            if ( $a->getWidgetPosition() == $b->getWidgetPosition() ) return 0;

            return ( $a->getWidgetPosition() > $b->getWidgetPosition() ) ? -1 : 1;
        });

        return view('dashboard::dashboard',compact('widgets'));
    }

    /**
     * Display the about this application info page
     *
     * @return \Illuminate\View\View
     */
    public function info()
    {
        $version = app('backend')->getVersion();

        return view('dashboard::info',compact('version'));
    }
}