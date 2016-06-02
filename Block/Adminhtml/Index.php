<?php
namespace CloudFlare\Plugin\Block\Adminhtml;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Backend\Model\UrlInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $assetFactory;
    protected $urlBuilder;

    const COMPILED_JS_PATH = "js/compiled.js";

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder
    ) {
        $this->assetRepository = $context->getAssetRepository();
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    protected function _prepareLayout() {
        //Generate link to CloudFlare/Plugin/view/web/js/compiled.js
        $asset = $this->assetRepository->createAsset('CloudFlare_Plugin::js/compiled.js');
        $compiledJsUrl = $asset->getUrl();
        $this->setCompiledJsUrl($compiledJsUrl);

        $restProxyPrefix = str_replace(self::COMPILED_JS_PATH, "", $compiledJsUrl);
        $this->setRestProxyPrefix($restProxyPrefix);

        $this->setProxyUrl($this->urlBuilder->getUrl("cloudflare/plugin/proxy"));
    }
}