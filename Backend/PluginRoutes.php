<?php

namespace CloudFlare\Plugin\Backend;

class PluginRoutes
{
    public static $routes = array(
        'account' => array(
            'class' => 'CloudFlare\Plugin\Backend\PluginActions',
            'methods' => array(
                'POST' => array(
                    'function' => 'postAccountSaveAPICredentials'
                )
            )
        ),
        'plugin/:id/settings' => array(
            'class' => 'CloudFlare\Plugin\Backend\PluginActions',
            'methods' => array(
                'GET' => array(
                    'function' => 'getPluginSettings'
                )
            )
        )
    );
}
