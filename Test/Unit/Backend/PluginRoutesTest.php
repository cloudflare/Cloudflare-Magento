<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CloudFlare\Plugin\Backend\PluginRoutes;

class PluginRoutesTest extends \PHPUnit_Framework_TestCase
{

    public function testGetPluginRoutesUpdatesRouteClass()
    {
        $routes = array(
            'account' => array(
                'class' => 'Any/Other/Class'
            )
        );

        $newRoutes = PluginRoutes::getRoutes($routes);

        $this->assertEquals('\CloudFlare\Plugin\Backend\PluginActions', $newRoutes['account']['class']);
    }
}
