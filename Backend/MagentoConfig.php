<?php

namespace CloudFlare\Plugin\Backend;

class MagentoConfig extends \CF\Integration\DefaultConfig implements \CF\Integration\ConfigInterface
{
    protected $config;

    public function __construct()
    {
        /*
         * Magento doesn't need a config but di.xml struggles to pass in a stringified
         * empty JSON object for reasons I will never understand so I made this empty
         * class for the Object Manager to inject so everyone is happy.
         */
        $this->config = [];
    }

    /**
     * @param Array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}
