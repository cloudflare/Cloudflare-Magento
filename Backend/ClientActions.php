<?php

namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DefaultIntegration;
use \CF\API\APIInterface;
use \CF\API\Request;

class ClientActions
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

    /*
     * GET /zones
     *
     * To ensure the plugin can only be used to manage the current Magento installation we
     * hook on this call to only return a list of size one of the current domain.
     */
    public function getZonesReturnMagentoZone() {
        $magentoDomainName = $this->integrationAPI->getMagentoDomainName();
        $this->request->setParameters(array('name' => $magentoDomainName));

        $response = $this->api->callAPI($this->request);

        if($this->api->responseOk($response)) {
            if(count($response["result"]) === 0) {
               array_push($response["result"], array(
                        'name' => $magentoDomainName,
                        'plan' => array('name' => ''),
                        'type' => '',
                        'status' => 'inactive',
                    )
                );
            }
        }

        return $response;
    }
}
