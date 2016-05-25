<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;

class Proxy extends AbstractAction {

    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute() {
        $request = $this->getRequest();
        $this->logger->info("Method: ". $request->getMethod());
        $this->logger->info("Params: ". print_r($request->getParams(),true));
        $this->logger->info("Body: ". print_r(json_decode(file_get_contents('php://input'), true),true));
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData($request->getMethod());
    }

    /*
     * I don't know why https://github.com/magento/magento2/blob/develop/app/code/Magento/Backend/App/AbstractAction.php#L247
     * doesn't accept window.FORM_KEY so I overrode this method to return true.  Its okay because we use our own custom CSRF token.
     */
    public function _processUrlKeys() {
        return true;
    }
}