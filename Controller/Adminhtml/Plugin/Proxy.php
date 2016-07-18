<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Psr\Log\LoggerInterface;

use \CloudFlare\Plugin\Backend;
use \CloudFlare\Plugin\Model\KeyValueFactory;
use \CF\Integration\DefaultConfig;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\DeploymentConfig\Reader;


class Proxy extends AbstractAction {

    protected $clientAPIClient;
    protected $config;
    protected $configReader;
    protected $dataStore;
    protected $integrationContext;
    protected $keyValueFactory;
    protected $logger;
    protected $jsonBody;
    protected $magentoAPI;
    protected $pluginAPIClient;
    protected $storeManager;

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
        KeyValueFactory $keyValueFactory,
        StoreManagerInterface $storeManager,
        Reader $configReader

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->keyValueFactory = $keyValueFactory;
        $this->storeManager = $storeManager;

        $this->config = new DefaultConfig("[]"); //config only used for debug mode but we use monolog so not based on config anymore
        $this->configReader = $configReader;
        $this->magentoAPI = new Backend\MagentoAPI($this->configReader, $this->keyValueFactory, $this->storeManager, $this->logger);
        $this->dataStore = new Backend\DataStore($this->magentoAPI);
        $this->integrationContext = new \CF\Integration\DefaultIntegration($this->config, $this->magentoAPI, $this->dataStore, $this->logger);
        $this->clientAPIClient = new \CF\API\Client($this->integrationContext);
        $this->pluginAPIClient = new \CF\API\Plugin($this->integrationContext);

        // php://input can only be read once
        $decodedJson = json_decode(file_get_contents('php://input'), true);
        if(json_last_error() !== 0) {
            $this->logger->error("Error decoding JSON: ". json_last_error_msg());
        }
        $this->jsonBody = $decodedJson;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute() {
        $result = $this->resultJsonFactory->create();

        $magentoRequest = $this->getRequest();
        $method =  $magentoRequest->getMethod();
        $parameters = $magentoRequest->getParams();
        $body = $this->getJsonBody();
        $path = (strtoupper($method === "GET") ? $parameters['proxyURL'] : $body['proxyURL']);

        $request = new \CF\API\Request($method, $path, $parameters, $body);

        $apiClient = null;
        $routes = null;
        if($this->isClientAPI($path)) {
            $apiClient = $this->clientAPIClient;
            $routes = Backend\ClientRoutes::$routes;
        } else if($this->isPluginAPI($path)) {
            $apiClient = $this->pluginAPIClient;
            $routes = Backend\PluginRoutes::$routes;
        } else {
            $this->logger->error("Bad Request: ". $request->getUrl());
            return $result->setData($this->clientAPIClient->createAPIError("Bad Request: ". $request->getUrl()));
        }

        $router = new \CF\Router\DefaultRestAPIRouter($this->integrationContext, $apiClient, $routes);
        $response = $router->route($request);

        return $result->setData($response);
    }

    public function getJsonBody() {
        return $this->jsonBody;
    }

    /**
     * @param $jsonBody
     */
    public function setJsonBody($jsonBody) {
        $this->jsonBody = $jsonBody;
    }

    /**
     * @param $path
     * @return bool
     */
    public function isClientAPI($path) {
        return (strpos($path, \CF\API\Client::ENDPOINT) !== false);
    }

    /**
     * @param $path
     * @return bool
     */
    public function isPluginAPI($path) {
        return (strpos($path, \CF\API\Plugin::ENDPOINT) !== false);
    }

    /*
     * Magento CSRF validation can't find the CSRF Token "form_key" if its in the JSON
     * so we copy it from the JSON body to the Magento request parameters.
    */
    public function _processUrlKeys() {
        $requestJsonBody = $this->getJsonBody();
        if($requestJsonBody !== null && array_key_exists(self::FORM_KEY, $requestJsonBody)) {
            $this->setJsonFormTokenOnMagentoRequest($requestJsonBody[self::FORM_KEY], $this->getRequest());
        }
        return parent::_processUrlKeys();
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