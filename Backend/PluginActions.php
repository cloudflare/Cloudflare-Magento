<?php

namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DefaultIntegration;
use \CF\API\APIInterface;
use \CF\API\Request;

class PluginActions
{
    protected $api;
    protected $config;
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
     * GET /settings
     */
    public function getPluginSettings() {
        return $this->api->createAPISuccessResponse(array($this->api->createPluginResult(\CF\API\Plugin::SETTING_DEFAULT_SETTINGS, false, true, '')));
    }
}
