<?php

namespace CloudFlare\Plugin\Backend;

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
     * @param MagentoIntegration $magentoIntegration
     * @param APIInterface $api
     * @param Request $request
     */
    public function __construct(MagentoIntegration $magentoIntegration, APIInterface $api, Request $request)
    {
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
    public function getZonesReturnMagentoZone()
    {
        $magentoDomainName = $this->integrationAPI->getMagentoDomainName();

        $response = $this->api->callAPI($this->request);
        if ($this->api->responseOk($response)) {
            $magentoZone = null;
            $bestMatch = strlen($magentoDomainName);
            foreach ($response['result'] as $zone) {
                $firstOccurrence = strpos($magentoDomainName, $zone['name']);
                if ($firstOccurrence !== false && $firstOccurrence < $bestMatch) {
                    $bestMatch = $firstOccurrence;
                    $magentoZone = $zone;
                }
            }
            if ($magentoZone === null) {
                $this->logger->warning($magentoDomainName . 'doesn\'t appear to be provisioned on CloudFlare.com.');
                $magentoZone =  array(
                    'name' => $magentoDomainName,
                    'plan' => array('name' => ''),
                    'type' => '',
                    'status' => 'inactive',
                );
            }
            $response['result'] = array($magentoZone);
        }

        return $response;
    }
}
