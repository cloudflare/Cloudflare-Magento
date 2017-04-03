<?php

namespace CloudFlare\Plugin\Backend;

use \CF\API\APIInterface;
use \CF\API\Request;
use \Psr\Log\LoggerInterface;

class MagentoHttpClient implements \CF\API\HttpClientInterface
{
    protected $endpoint;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function send(Request $request)
    {
        $client = $this->createZendClient($request);
        try {
            $response = $client->request();
            return json_decode($response->getBody(), true);
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logAPICall($this->endpoint, array(
                'type' => 'request',
                'method' => $request->getMethod(),
                'path' => $request->getUrl(),
                'headers' => $request->getHeaders(),
                'params' => $request->getParameters(),
                'body' => $request->getBody()), true);
            $this->logAPICall($this->endpoint, array('type' => 'response', 'code' => $e->getCode(), 'body' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString()), true);
        }

        return null;
    }

    /**
     * @param  Request $request
     * @return ZendClient $client
     */
    public function createZendClient(Request $request)
    {
        $client = new \Zend_Http_Client();
        $client->setUri($this->endpoint . $request->getUrl());

        $client->setMethod($request->getMethod());

        $client->setHeaders($request->getHeaders());
        $client->setParameterGet($request->getParameters());

        if ($request->getMethod() !== 'GET') {
            $client->setRawData(json_encode($request->getBody()), 'application/json');
        }

        return $client;
    }

    /**
     * @param string $apiName
     * @param array  $message
     * @param bool   $isError
     */
    public function logAPICall($apiName, $message, $isError)
    {
        $logLevel = 'error';
        if ($isError === false) {
            $logLevel = 'debug';
        }
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        $this->logger->$logLevel('['.$apiName.'] '.$message);
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
