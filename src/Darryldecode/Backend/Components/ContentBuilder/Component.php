<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 2/6/2015
 * Time: 12:45 PM
 */

namespace Darryldecode\Backend\Components\ContentBuilder;


use Darryldecode\Backend\Components\ContentBuilder\Models\ContentType;
use Darryldecode\Backend\Base\Registrar\ComponentInterface;
use Darryldecode\Backend\Base\Registrar\ComponentNavigation;
use Darryldecode\Backend\Base\Registrar\ComponentNavigationCollection;

class Component implements ComponentInterface {

    /**
     * returns the component name
     *
     * @return array
     */
    public function getComponentInfo()
    {
        return array(
            'name' => 'Content Builder',
            'description' => 'A Content Builder that let\'s you build flexible content types like blog, events etc',
        );
    }

    /**
     * returns the available permissions of the component
     *
     * @return array
     */
    public function getAvailablePermissions()
    {
        $availablePermissions = array(
            array(
                'title' => 'Manage Content Builder',
                'description' => 'Enable\'s the user to manage Content Builder. This includes access and modification capabilities',
                'permission' => 'contentBuilder.manage',
            ),
            array(
                'title' => 'Delete Content Builder Entry',
                'description' => 'Gives the user authorization to perform delete action in Content Builder.',
                'permission' => 'contentBuilder.delete',
            ),
        );

        // when "php artisan" command is run, all application registered services are run,
        // so during "php artisan package:publish" stuffs this will throw error as ContentType::all()
        // has no migrations yet. Let's try catch here so we can solve that problem

        try {

            foreach(ContentType::all() as $type)
            {
                array_push($availablePermissions, array(
                    'title' => 'Manage '.$type->type,
                    'description' => 'Enable\'s the user to manage '.$type->type.'. This includes access and modification capabilities',
                    'permission' => $type->type.'.manage',
                ));
                array_push($availablePermissions, array(
                    'title' => 'Delete '.$type->type.' Entry',
                    'description' => 'Authorize user to perform delete action any '.$type->type.' entries.',
                    'permission' => $type->type.'.delete',
                ));
            }

        } catch(\Exception $e) {

            // there is an error, do nothing
        }

        return $availablePermissions;
    }

    /**
     * the component navigation
     *
     * @return ComponentNavigationCollection
     */
    public function getNavigation()
    {
        // the content builder navigation
        $contentBuilder = new ComponentNavigation('Content Builder','fa fa-th-large',url(config('backend.backend.base_url').'/content_types'));
        $contentBuilder->setRequiredPermissions(['contentBuilder.manage']);

        // the form group:form builder:custom fields
        $formBuilder = new ComponentNavigation('Custom Fields','fa fa-list',url(config('backend.backend.base_url').'/custom_fields'));
        $formBuilder->setRequiredPermissions(['contentBuilder.manage']);


        // the contents navigation
        $contentsNavigation = new ComponentNavigation('Contents','fa fa-book','',true);

        // when "php artisan" command is run, all application registered services are run,
        // so during "php artisan package:publish" stuffs this will throw error as ContentType::all()
        // has no migrations yet. Let's try catch here so we can solve that problem
        try {

            foreach(ContentType::all() as $type)
            {
                $n = new ComponentNavigation(
                    $type->type,
                    'fa fa-pencil-square',
                    url(config('backend.backend.base_url').'/contents/'.$type->type)
                );

                // the permission requirements to check
                $permissionManage = $type->type.'.manage';

                $n->setRequiredPermissions([$permissionManage]);

                $contentsNavigation->addSubMenu($n);
            }
        } catch(\Exception $e) {

            // there is an error, do nothing
        }

        $navs = new ComponentNavigationCollection();
        $navs->push($contentBuilder);
        $navs->push($formBuilder);
        $navs->push($contentsNavigation);

        return $navs;
    }

    /**
     * returns the views path
     *
     * @return array
     */
    public function getViewsPath()
    {
        return array(
            'dir' => __DIR__.'/Views',
            'namespace' => 'contentBuilder',
        );
    }

    /**
     * get components routes path
     *
     * @return array
     */
    public function getRoutesControl()
    {
        return array(
            'dir' => __DIR__.'/routes.php',
            'namespace' => 'Darryldecode\\Backend\\Components\\ContentBuilder\\Controllers',
        );
    }

    /**
     * get component scripts for header
     *
     * @return array
     */
    public function getHeaderScripts()
    {
        return array();
    }

    /**
     * get component scripts for footer
     *
     * @return array
     */
    public function getFooterScripts()
    {
        return array();
    }
}

return new Component;