<?php
namespace CloudFlare\Plugin\Block\Adminhtml;

use \CloudFlare\Plugin\Backend\DataStore;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Backend\Model\UrlInterface;
use \CloudFlare\Plugin\Model\KeyValueFactory;
use \CloudFlare\Plugin\Backend\MagentoAPI;
use \Magento\Framework\App\DeploymentConfig\Reader;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $assetFactory;
    protected $configReader;
    protected $dataStore;
    protected $keyValueModelFactory;
    protected $logger;
    protected $magentoAPI;
    protected $urlBuilder;

    const COMPILED_JS_PATH = "js/compiled.js";

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param KeyValueFactory $keyValueModelFactory
     * @internal param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        KeyValueFactory $keyValueModelFactory,
        Reader $configReader
    ) {
        $this->assetRepository = $context->getAssetRepository();
        $this->configReader = $configReader;
        $this->keyValueModelFactory = $keyValueModelFactory;
        $this->logger = $context->getLogger();
        $this->urlBuilder = $urlBuilder;

        $this->magentoAPI = new MagentoAPI($this->configReader, $this->keyValueModelFactory, $context->getStoreManager(), $this->logger);
        $this->dataStore = new DataStore($this->magentoAPI);

        parent::__construct($context);
    }

    /*
     * $this->set*() are "magic" in that you can call set[THING]() and magento store and expose a
     * get[THING]() for you to retrieve the value on the front end.
     */
    protected function _prepareLayout() {
        //Generate link to CloudFlare/Plugin/view/web/js/compiled.js
        $asset = $this->assetRepository->createAsset('CloudFlare_Plugin::js/compiled.js');
        $compiledJsUrl = $asset->getUrl();
        $this->setCompiledJsUrl($compiledJsUrl);

        $restProxyPrefix = str_replace(self::COMPILED_JS_PATH, "", $compiledJsUrl);
        $this->setRestProxyPrefix($restProxyPrefix);

        $this->setProxyUrl($this->urlBuilder->getUrl("cloudflare/plugin/proxy"));
        $this->setCloudflareEmail($this->dataStore->getCloudFlareEmail());
    }
}