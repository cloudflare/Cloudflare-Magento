<?php
namespace CloudFlare\Plugin\Backend;

use CF\API\HttpClientInterface;
use CF\Integration\DefaultIntegration;
use CF\Integration\IntegrationInterface;
use CF\Integration\ConfigInterface;
use CF\Integration\IntegrationAPIInterface;
use CF\Integration\DataStoreInterface;
use Psr\Log\LoggerInterface;

class MagentoIntegration extends \CF\Integration\DefaultIntegration implements \CF\Integration\IntegrationInterface
{
    protected $httpClient;

    /**
     * @param ConfigInterface         $config
     * @param IntegrationAPIInterface $integrationAPI
     * @param DataStoreInterface      $dataStore
     * @param LoggerInterface         $logger
     * @param HttpClientInterface     $httpClient
     */
    public function __construct(ConfigInterface $config, IntegrationAPIInterface $integrationAPI, DataStoreInterface $dataStore, LoggerInterface $logger, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        parent::__construct($config, $integrationAPI, $dataStore, $logger);
    }

    /**
     * @return HttpClientInterface $httpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
