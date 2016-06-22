<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;

use \CloudFlare\Plugin\Backend;
use \CloudFlare\Plugin\Model\KeyValueFactory;
use \CF\Integration\DefaultConfig;
use GuzzleHttp;


class Proxy extends AbstractAction {

    protected $clientAPIClient;
    protected $config;
    protected $dataStore;
    protected $integrationContext;
    protected $logger;
    protected $keyValueFactory;
    protected $magentoAPI;

    const FORM_KEY = "form_key";

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        KeyValueFactory $keyValueFactory

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->keyValueFactory = $keyValueFactory;

        $this->config = new DefaultConfig("[]"); //config only used for debug mode but we use monolog so not based on config anymore
        $this->magentoAPI = new Backend\MagentoAPI($this->keyValueFactory, $this->logger);
        $this->dataStore = new Backend\DataStore($this->magentoAPI);
        $this->integrationContext = new \CF\Integration\DefaultIntegration($this->config, $this->magentoAPI, $this->dataStore, $this->logger);
        $this->clientAPIClient = new \CF\API\Client($this->integrationContext);

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute() {
        $magentoRequest = $this->getRequest();
        $method =  $magentoRequest->getMethod();
        $parameters = $magentoRequest->getParams();
        $body = $this->getJSONBody();
        $path = (strtoupper($method === "GET") ? $parameters['proxyURL'] : $body['proxyURL']);

        $request = new \CF\API\Request($method, $path, $parameters, $body);

        $response = "";
        if($this->isClientAPI($path)) {
            $clientRouter = new \CF\Router\DefaultRestAPIRouter($this->integrationContext, $this->clientAPIClient, Backend\ClientRoutes::$routes);
            $response = $clientRouter->route($request);
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($response);
    }

    public function isClientAPI($path) {
        return (strpos($path, \CF\API\Client::ENDPOINT) !== false);
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