<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;

use \CloudFlare\Plugin\Backend;
use \CF\Integration\DefaultConfig;
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
        $config = new DefaultConfig("[]");
        $magentoAPI = new Backend\MagentoAPI();
        $dataStore = new Backend\DataStore();
        $integrationContext = new \CF\Integration\DefaultIntegration($config, $magentoAPI, $dataStore, $this->logger);

        $magentoRequest = $this->getRequest();
        $method =  $magentoRequest->getMethod();
        $parameters = $magentoRequest->getParams();
        $body = $this->getJSONBody();
        $path = (strtoupper($method === "GET") ? $_GET['proxyURL'] : $body['proxyURL']);

        $request = new \CF\API\Request($method, $path, $parameters, $body);

        $clientAPIClient = new \CF\API\Client($integrationContext);

        $result = $this->resultJsonFactory->create();
        return $result->setData($clientAPIClient->callAPI($request));
    }

    /*
     * Magento CSRF validation can't find the CSRF Token "form_key" if its in the JSON
     * so we copy it from the JSON body to the Magento request parameters.
    */
    public function _processUrlKeys() {
        $requestJsonBody = $this->getJSONBody();
        if($requestJsonBody !== null && array_key_exists(self::FORM_KEY, $requestJsonBody)) {
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