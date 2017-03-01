<?php

namespace CloudFlare\Plugin\Backend;

class PluginRoutes extends \CF\API\PluginRoutes
{
    /**
     * @param $routeList
     * @return mixed
     */
    public static function getRoutes($routeList)
    {

        foreach ($routeList as $routePath => $route) {
            $route['class'] = '\CloudFlare\Plugin\Backend\PluginActions';
            $routeList[$routePath] = $route;
        }

        return $routeList;
    }
}
