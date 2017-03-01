<?php
namespace CloudFlare\Plugin\Controller\Adminhtml\Plugin;

use \CF\API\Request;
use \CF\Integration\DefaultConfig;
use \CF\Integration\DefaultIntegration;
use \CF\Router\RequestRouter;
use \CloudFlare\Plugin\Backend\ClientAPI;
use \CloudFlare\Plugin\Backend\ClientRoutes;
use \CloudFlare\Plugin\Backend\DataStore;
use \CloudFlare\Plugin\Backend\MagentoAPI;
use \CloudFlare\Plugin\Backend\PluginRoutes;

use \Magento\Backend\App\AbstractAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface;

class Proxy extends AbstractAction
{

    protected $dataStore;
    protected $integrationContext;
    protected $logger;
    protected $jsonBody;
    protected $magentoAPI;
    protected $resultJsonFactory;
    protected $requestRouter;

    const FORM_KEY = "form_key";


    /**
     * @param Context $context
     * @param Backend\DataStore|DataStore $dataStore
     * @param DefaultIntegration $integrationContext
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Backend\MagentoAPI|MagentoAPI $magentoAPI
     * @param RequestRouter $requestRouter
     */
    public function __construct(
        Context $context,
        DataStore $dataStore,
        DefaultIntegration $integrationContext,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        MagentoAPI $magentoAPI,
        RequestRouter $requestRouter
    ) {
        $this->dataStore = $dataStore;
        $this->integrationContext = $integrationContext;
        $this->logger = $logger;
        $this->magentoAPI = $magentoAPI;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->requestRouter = $requestRouter;

        $this->requestRouter->addRouter('\CloudFlare\Plugin\Backend\ClientAPI', ClientRoutes::$routes);
        $this->requestRouter->addRouter('\CF\API\Plugin', PluginRoutes::getRoutes(\CF\API\PluginRoutes::$routes));

        // php://input can only be read once
        $decodedJson = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== 0) {
            $this->logger->error("Error decoding JSON: ". json_last_error_msg());
        }
        $this->jsonBody = $decodedJson;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $magentoRequest = $this->getRequest();
        $method =  $magentoRequest->getMethod();
        $parameters = $magentoRequest->getParams();
        $body = $this->getJsonBody();
        $path = (strtoupper($method === "GET") ? $parameters['proxyURL'] : $body['proxyURL']);

        $request = new Request($method, $path, $parameters, $body);

        $response = $this->requestRouter->route($request);

        return $result->setData($response);
    }

    public function getJsonBody()
    {
        return $this->jsonBody;
    }

    /**
     * @param $jsonBody
     */
    public function setJsonBody($jsonBody)
    {
        $this->jsonBody = $jsonBody;
    }

    /*
     * Magento CSRF validation can't find the CSRF Token "form_key" if its in the JSON
     * so we copy it from the JSON body to the Magento request parameters.
    */
    public function _processUrlKeys()
    {
        $requestJsonBody = $this->getJsonBody();
        if ($requestJsonBody !== null && array_key_exists(self::FORM_KEY, $requestJsonBody)) {
            $this->setJsonFormTokenOnMagentoRequest($requestJsonBody[self::FORM_KEY], $this->getRequest());
        }
        return parent::_processUrlKeys();
    }

    /**
     * @param $token "form_key"
     * @param $request
     */
    public function setJsonFormTokenOnMagentoRequest($token, $request)
    {
        $parameters = $request->getParams();
        $parameters[self::FORM_KEY] = $token;
        $request->setParams($parameters);
        return $request;
    }
}
