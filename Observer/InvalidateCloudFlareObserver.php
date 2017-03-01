<?php

namespace CloudFlare\Plugin\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\PageCache\Model\Config;
use \CloudFlare\Plugin\Backend\CacheTags;

class InvalidateCloudFlareObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \CloudFlare\Plugin\Backend\CacheTags
     */
    protected $cacheTags;


    /**
     * @param Config $config
     * @param CacheTags $cacheTags
     */
    public function __construct(
        Config $config,
        CacheTags $cacheTags
    ) {
        $this->config = $config;
        $this->cacheTags = $cacheTags;
    }

    /**
     * Purges CloudFlare cache by tag
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return;
        }
        $object = $observer->getEvent()->getObject();

        if ($object instanceof \Magento\Framework\DataObject\IdentityInterface === false
        || empty($object->getIdentities())) {
            return;
        }

        $this->cacheTags->purgeCacheTags($object->getIdentities());
    }
}
