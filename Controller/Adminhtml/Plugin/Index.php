<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface; // Needed to retrieve config values
use \Psr\Log\LoggerInterface;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var scopeConfig
     * Needed to retrieve config values
     */
    protected $scopeConfig;

    protected $logger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        // Needed to retrieve config values
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig; // Needed to retrieve config values
        $this->logger = $logger;
    }

    /**
     * Index Action*
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CloudFlare_Plugin::index');
        $resultPage->addBreadcrumb(__('System'), __('System'));
        $resultPage->addBreadcrumb(__('CloudFlare'), __('Cloudflare'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cloudflare'));
        return $resultPage;
    }
}
