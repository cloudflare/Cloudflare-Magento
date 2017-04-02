<?php
namespace CloudFlare\Plugin\Backend;

use \CF\API\Plugin;
use \CF\API\Request;
use CF\Integration\IntegrationInterface;

class PluginAPI extends Plugin
{
    protected $magentoHttpClient;

    public function __construct(IntegrationInterface $integration, MagentoHttpClient $magentoHttpClient)
    {
        parent::__construc($integration);

        $this->magentoHttpClient = $magentoHttpClient;
        $this->magentoHttpClient->setEndpoint($this->getEndpoint());
        $this->setHttpClient($this->magentoHttpClient);
    }
}
