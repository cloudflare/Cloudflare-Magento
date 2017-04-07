<?php

namespace CloudFlare\Plugin\Backend;

use \CF\API\APIInterface;
use \CF\API\Request;
use \CF\API\AbstractPluginActions;
use \CF\API\Exception\ZoneSettingFailException;

class PluginActions extends AbstractPluginActions
{
    protected $api;
    protected $config;
    protected $clientAPI;
    protected $integrationAPI;
    protected $dataStore;
    protected $logger;
    protected $request;

    //CloudFlare API doesn't have error codes for these :(
    private static $upgradePlanErrors = array(
        "Not allowed to edit setting for mirage",
        "Not allowed to edit setting for polish",
    );

    public function __construct(MagentoIntegration $defaultIntegration, APIInterface $api, Request $request)
    {
        parent::__construct($defaultIntegration, $api, $request);
        $this->clientAPI = new clientAPI($defaultIntegration);
    }

    /*
     * PATCH /plugin/:id/settings/default_settings
     */
    public function applyDefaultSettings()
    {
        $pathArray = explode('/', $this->request->getUrl());
        $zoneId = $pathArray[1];

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/security_level', array(), array('value' => 'medium'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/cache_level', array(), array('value' => 'basic'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/minify', array(), array('value' => array('css' => 'on', 'html' => 'off', 'js' => 'off')));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/browser_cache_ttl', array(), array('value' => 14400)); //4 hours

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/always_online', array(), array('value' => 'on'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/development_mode', array(), array('value' => 'off'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/ipv6', array(), array('value' => 'off'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/websockets', array(), array('value' => 'on'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/ip_geolocation', array(), array('value' => 'on'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/email_obfuscation', array(), array('value' => 'on'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/server_side_exclude', array(), array('value' => 'on'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/hotlink_protection', array(), array('value' => 'off'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/polish', array(), array('value' => 'off'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/mirage', array(), array('value' => 'off'));

        $this->patchZoneSetting('zones/'. $zoneId .'/settings/rocket_loader', array(), array('value' => 'off'));

        $adminUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/' . $this->integrationAPI->getMagentoAdminPath() . "*";
        $checkoutUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/checkout*';
        $setupUrlPattern = $this->integrationAPI->getMagentoDomainName() . '/setup*';

        $this->postPageRule($zoneId, $this->createPageRuleDisablePerformanceCacheBypassJsonBody($adminUrlPattern));
        $this->postPageRule($zoneId, $this->createPageRuleDisablePerformanceCacheBypassJsonBody($checkoutUrlPattern));
        $this->postPageRule($zoneId, $this->createPageRuleDisablePerformanceCacheBypassJsonBody($setupUrlPattern));
    }

    /**
     * @param $url
     * @param $parameters
     * @param $body
     * @return bool
     * @throws ZoneSettingFailException
     */
    public function patchZoneSetting($url, $parameters, $body)
    {
        $response = $this->clientAPI->callAPI(new \CF\API\Request('PATCH', $url, $parameters, $body));

        if (!$this->clientAPI->responseOk($response)) {
            foreach ($response['errors'] as $error) {
                if (in_array($error['message'], self::$upgradePlanErrors)) {
                    //error is related to upgrading the plan.
                    return true;
                }
            }
            throw new ZoneSettingFailException();
        }
    }

    /**
     * @param $zoneId
     * @param $body
     * @throws ZoneSettingFailException
     */
    public function postPageRule($zoneId, $body)
    {
        $response = $this->clientAPI->callAPI(new \CF\API\Request('POST', 'zones/'. $zoneId .'/pagerules', array(), $body));
        if (!$this->clientAPI->responseOk($response)) {
            throw new ZoneSettingFailException();
        }
    }

    /**
     * @param $urlPattern
     * @return array
     */
    public function createPageRuleDisablePerformanceCacheBypassJsonBody($urlPattern)
    {
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
