<?php

namespace CloudFlare\Plugin\Backend;

class ClientRoutes
{
    public static $routes = array(
        'zones' => array(
            'class' => 'CloudFlare\Plugin\Backend\ClientActions',
            'methods' => array(
                'GET' => array(
                    'function' => 'getZonesReturnMagentoZone'
                )
            )
        )
    );
}
