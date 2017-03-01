<?php

namespace CloudFlare\Plugin\Model\Layout;

use \Magento\Framework\App\ResponseInterface;
use \Magento\PageCache\Model\Config;
use \Psr\Log\LoggerInterface;
use \CloudFlare\Plugin\Backend\CacheTags;

class LayoutPlugin
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \CloudFlare\Plugin\Backend\CacheTagsUtil
     */
    protected $cacheTagsUtil;


    /**
     * Constructor
     *
     * @param ResponseInterface $response
     * @param Config $config
     * @param LoggerInterface $logger
     * @param CacheTagsUtil $cacheTagsUtil
     */
    public function __construct(
        ResponseInterface $response,
        Config $config,
        LoggerInterface $logger,
        CacheTags $cacheTagsUtil
    ) {
        $this->response = $response;
        $this->config = $config;
        $this->logger = $logger;
        $this->cacheTagsUtil = $cacheTagsUtil;
    }


    /**
     * Set X-Cache-Tags header with all the Magento Cache Tags so
     * they can be purged by the CloudFlare API
     *
     * @param \Magento\Framework\View\Layout $subject
     * @param $result
     * @return mixed
     */
    public function afterGetOutput(\Magento\Framework\View\Layout $subject, $result)
    {
        if (!$subject->isCacheable() || !$this->config->isEnabled()) {
            return $result;
        }

        $tags = [];
        foreach ($subject->getAllBlocks() as $block) {
            if ($block->getIdentities() !== null) {
                $tags = array_merge($tags, $block->getIdentities());
            }
        }
        $tags = array_unique($tags);

        $this->cacheTagsUtil->setCloudFlareCacheTagsResponseHeader($this->response, $tags);

        return $result;
    }
}
