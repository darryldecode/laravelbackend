<?php namespace Darryldecode\Backend\Widgets\Welcome;

use Darryldecode\Backend\Base\Registrar\WidgetInterface;

class Widget implements WidgetInterface
{
    /**
     * get the widget information
     *
     * @return array
     */
    public function getWidgetInfo()
    {
        return array(
            'name' => 'Dashboard Welcome Message',
            'description' => 'The dashboard welcome message widget.'
        );
    }

    /**
     * get the widget template
     *
     * @return string
     */
    public function getWidgetTemplate()
    {
        return __DIR__.'/widget-view.blade.php';
    }

    /**
     * the widget position
     *
     * @return int
     */
    public function getWidgetPosition()
    {
        return 5;
    }

    /**
     * determine if widget is needed to be loaded or not
     *
     * @return bool
     */
    public function isWidgetActive()
    {
        return true;
    }

    /**
     * get component scripts for header
     *
     * @return array
     */
    public function getHeaderScripts()
    {
        // TODO: Implement getHeaderScripts() method.
    }

    /**
     * get component scripts for footer
     *
     * @return array
     */
    public function getFooterScripts()
    {
        // TODO: Implement getFooterScripts() method.
    }
}

return new Widget();