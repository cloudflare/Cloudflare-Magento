<?php
namespace CloudFlare\Plugin\Block\Adminhtml;

use \CloudFlare\Plugin\Backend\MagentoAPI;
use \CloudFlare\Plugin\Backend\DataStore;
use \Magento\Backend\Model\UrlInterface;
use \Magento\Framework\View\Element\Template\Context;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $assetFactory;
    protected $dataStore;
    protected $logger;
    protected $magentoAPI;
    protected $urlBuilder;

    const COMPILED_JS_PATH = "js/compiled.min.js";

    /**
     * @param Context $context
     * @param DataStore $dataStore
     * @param MagentoAPI $magentoAPI
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        DataStore $dataStore,
        MagentoAPI $magentoAPI,
        UrlInterface $urlBuilder
    ) {
        $this->assetRepository = $context->getAssetRepository();
        $this->dataStore = $dataStore;
        $this->magentoAPI = $magentoAPI;
        $this->logger = $context->getLogger();
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context);
    }

    /*
     * $this->set*() are "magic" in that you can call set[THING]() and magento store and expose a
     * get[THING]() for you to retrieve the value on the front end.
     */
    protected function _prepareLayout()
    {
        //Generate link to CloudFlare/Plugin/view/web/js/compiled.min.js
        $asset = $this->assetRepository->createAsset('CloudFlare_Plugin::js/compiled.min.js');
        $compiledJsUrl = $asset->getUrl();
        $this->setCompiledJsUrl($compiledJsUrl);

        $restProxyPrefix = str_replace(self::COMPILED_JS_PATH, "", $compiledJsUrl);
        $this->setRestProxyPrefix($restProxyPrefix);

        $this->setProxyUrl($this->urlBuilder->getUrl("cloudflare/plugin/proxy"));
        $this->setCloudflareEmail($this->dataStore->getCloudFlareEmail());
    }
}
