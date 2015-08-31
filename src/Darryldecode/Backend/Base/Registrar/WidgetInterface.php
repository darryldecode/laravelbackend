<?php

namespace Darryldecode\Backend\Base\Registrar;


interface WidgetInterface {

    /**
     * get the widget information
     *
     * @return array
     */
    public function getWidgetInfo();

    /**
     * the widget template
     *
     * @return callable
     */
    public function getWidgetTemplate();

    /**
     * the widget position. The greater the value, the higher the priority
     *
     * @return int
     */
    public function getWidgetPosition();

    /**
     * determine if widget is needed to be loaded or not
     *
     * @return bool
     */
    public function isWidgetActive();

    /**
     * get component scripts for header
     *
     * @return array
     */
    public function getHeaderScripts();

    /**
     * get component scripts for footer
     *
     * @return array
     */
    public function getFooterScripts();
}