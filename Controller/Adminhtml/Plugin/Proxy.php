<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;
use GuzzleHttp;

class Proxy extends AbstractAction {

    protected $logger;

    const FORM_KEY = "form_key";

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
        try {
            $client = new GuzzleHttp\Client(['base_url' => "test.com"]);
            $this->logger->info(print_r($client,true));
        } catch(GuzzleHttp\Exception\RequestException $e) {
            $this->logger->info(print_r($e,true));
        }

        return $result->setData($request->getMethod());
    }

    /*
     * Magento CSRF validation can't find the CSRF Token "form_key" if its in the JSON
     * so we copy it from the JSON body to the Magento request parameters.
    */
    public function _processUrlKeys() {
        $requestJsonBody = $this->getJSONBody();
        if(array_key_exists(self::FORM_KEY, $requestJsonBody)) {
            $this->setJsonFormTokenOnMagentoRequest($requestJsonBody[self::FORM_KEY], $this->getRequest());
        }
        return parent::_processUrlKeys();
    }

    public function getJSONBody() {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * @param $token "form_key"
     * @param $request
     */
    public function setJsonFormTokenOnMagentoRequest($token, $request) {
        $parameters = $request->getParams();
        $parameters[self::FORM_KEY] = $token;
        $request->setParams($parameters);
        return $request;
    }
}