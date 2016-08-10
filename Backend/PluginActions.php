<?php

namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DefaultIntegration;
use \CF\API\APIInterface;
use \CF\API\Request;
use \CF\API\AbstractPluginActions;

class PluginActions extends AbstractPluginActions
{
    protected $api;
    protected $config;
    protected $clientAPI;
    protected $integrationAPI;
    protected $dataStore;
    protected $logger;
    protected $request;

    /*
     * PATCH /plugin/:id/settings/default_settings
     */
    public function applyDefaultSettings() {
        $pathArray = explode('/', $this->request->getUrl());
        $zoneId = $pathArray[1];

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/security_level', array(), array('value' => 'medium')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/cache_level', array(), array('value' => 'basic')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/minify', array(), array('value' => array('css' => 'on', 'html' => 'off', 'js' => 'off'))));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/browser_cache_ttl', array(), array('value' => 14400))); //4 hours

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/always_online', array(), array('value' => 'on')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/development_mode', array(), array('value' => 'off')));

        $adminUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/' . $this->integrationAPI->getMagentoAdminPath() . "*";
        $checkoutUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/checkout*';
        $setupUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/setup*';

        $this->clientAPI->callAPI(new \CF\API\Request('POST', 'zones/'. $zoneId .'/pagerules', array(), $this->createPageRuleDisablePerformanceCacheBypassJsonBody($adminUrlPattern)));
        $this->clientAPI->callAPI(new \CF\API\Request('POST', 'zones/'. $zoneId .'/pagerules', array(), $this->createPageRuleDisablePerformanceCacheBypassJsonBody($checkoutUrlPattern)));
        $this->clientAPI->callAPI(new \CF\API\Request('POST', 'zones/'. $zoneId .'/pagerules', array(), $this->createPageRuleDisablePerformanceCacheBypassJsonBody($setupUrlPattern)));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/ipv6', array(), array('value' => 'off')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/websockets', array(), array('value' => 'on')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/ip_geolocation', array(), array('value' => 'on')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/email_obfuscation', array(), array('value' => 'on')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/server_side_exclude', array(), array('value' => 'on')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/hotlink_protection', array(), array('value' => 'off')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/polish', array(), array('value' => 'off')));
        
        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/mirage', array(), array('value' => 'off')));

        $this->clientAPI->callAPI(new \CF\API\Request('PATCH', 'zones/'. $zoneId .'/settings/rocket_loader', array(), array('value' => 'off')));
    }

    /**
     * @param $urlPattern
     * @return array
     */
    public function createPageRuleDisablePerformanceCacheBypassJsonBody($urlPattern) {
        return array(
            'targets' => array(
                array(
                    'target' => 'url',
                    'constraint' => array(
                        'operator' => 'matches',
                        'value' => $urlPattern
                    )
                )
            ),
            'actions' => array(
                array(
                    'id' => 'disable_performance'
                ),
                array(
                    'id' => 'cache_level',
                    'value' => 'bypass'
                )
            ),
            'status' => 'active'
        );
    }
}
