<?php

namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DefaultIntegration;
use \CF\API\APIInterface;
use \CF\API\Request;

class PluginActions
{
    protected $api;
    protected $config;
    protected $clientAPI;
    protected $integrationAPI;
    protected $dataStore;
    protected $logger;
    protected $request;

    /**
     * @param DefaultIntegration $magentoIntegration
     * @param APIInterface $api
     * @param Request $request
     */
    public function __construct(DefaultIntegration $magentoIntegration, APIInterface $api, Request $request) {
        $this->api = $api;
        $this->config = $magentoIntegration->getConfig();
        $this->integrationAPI = $magentoIntegration->getIntegrationAPI();
        $this->dataStore = $magentoIntegration->getDataStore();
        $this->logger = $magentoIntegration->getLogger();
        $this->request = $request;
        //plugin API needs to make client API calls as part of setting default settings.
        $this->clientAPI = new ClientAPI($magentoIntegration);
    }

    /**
     * @param $clientAPI
     */
    public function setClientAPI($clientAPI) {
        $this->clientAPI = $clientAPI;
    }


    /**
     * POST /account
     * body.apiKey
     * body.email
     */
    public function postAccountSaveAPICredentials() {
        $requestBody = $this->request->getBody();
        if(empty($requestBody["apiKey"])) {
            return $this->api->createAPIError("Missing required parameter: 'apiKey'.");
        }
        if(empty($requestBody["email"])) {
            return $this->api->createAPIError("Missing required parameter: 'email'.");
        }

        $this->dataStore->createUserDataStore($requestBody["apiKey"], $requestBody["email"], null, null);

        return $this->api->createAPISuccessResponse(array("email" => $this->dataStore->getCloudFlareEmail()));
    }

    /*
     * GET /plugin/:id/settings
     */
    public function getPluginSettings() {
        return $this->api->createAPISuccessResponse(array($this->api->createPluginResult(\CF\API\Plugin::SETTING_DEFAULT_SETTINGS, false, true, '')));
    }

    /*
     * PATCH /plugin/:id/settings/:human_readable_id
     */
    public function patchPluginSettings() {
        $pathArray = explode('/', $this->request->getUrl());
        $settingId = $pathArray[3];

        switch($settingId) {
            case \CF\API\Plugin::SETTING_DEFAULT_SETTINGS:
                return $this->patchPluginSettingsDefaultSettings();
                break;
            default:
                return $this->api->createAPIError($settingId . ' is not a valid setting id.');
        }
    }

    /*
     * PATCH /plugin/:id/settings/default_settings
     */
    public function patchPluginSettingsDefaultSettings() {
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

        return $this->api->createAPISuccessResponse($this->api->createPluginResult(\CF\API\Plugin::SETTING_DEFAULT_SETTINGS, true, true, ''));
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
