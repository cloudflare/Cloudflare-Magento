<?php

namespace CloudFlare\Plugin\Observer;

use \CloudFlare\Plugin\Backend\CacheTags;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\PageCache\Model\Config;
use \Magento\Framework\Event\Observer;

class FlushAllCloudFlareObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    protected $cacheTags;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \CloudFlare\Plugin\Backend\CacheTags $cacheTags
     */
    public function __construct(
        Config $config,
        CacheTags $cacheTags
    ) {
        $this->config = $config;
        $this->cacheTags = $cacheTags;
    }

    /**
     * Flush CloudFlare cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isEnabled()) {
            $this->cacheTags->purgeCache();
        }
    }
}
