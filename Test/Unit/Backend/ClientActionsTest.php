<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use \CloudFlare\Plugin\Backend\ClientActions;
use \CloudFlare\Plugin\Backend\MagentoIntegration;
use \CF\API\Request;

class ClientActionsTest extends \PHPUnit_Framework_TestCase
{
    protected $clientActions;
    protected $mockConfig;
    protected $mockDataStore;
    protected $mockIntegrationContext;
    protected $mockLogger;
    protected $mockMagentoAPI;
    protected $mockClientAPIClient;
    protected $mockRequest;
    protected $mockHttpClient;

    public function setUp()
    {
        $this->mockConfig = $this->getMockBuilder('\CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('\CloudFlare\Plugin\Backend\DataStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockMagentoAPI = $this->getMockBuilder('\CloudFlare\Plugin\Backend\MagentoAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockClientAPIClient = $this->getMockBuilder('\CF\API\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockRequest = $this->getMockBuilder('\CF\API\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockHttpClient = $this->getMockBuilder('\CF\API\HttpClientInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockIntegrationContext = new MagentoIntegration(
            $this->mockConfig,
            $this->mockMagentoAPI,
            $this->mockDataStore,
            $this->mockLogger,
            $this->mockHttpClient
        );
        $this->clientActions = new ClientActions(
            $this->mockIntegrationContext,
            $this->mockClientAPIClient,
            $this->mockRequest
        );
    }

    public function testGetZonesReturnMagentoZoneReturnsZoneIfItExists()
    {
        $domain = 'domain.com';
        $this->mockMagentoAPI->method('getMagentoDomainName')->willReturn($domain);
        $testResult = array('name' => $domain);
        $this->mockClientAPIClient->method('callAPI')->willReturn(
            array(
                "success" => true,
                "result" => array($testResult)
            )
        );
        $this->mockClientAPIClient->method('responseOk')->willReturn(true);

        $response = $this->clientActions->getZonesReturnMagentoZone();

        $this->assertEquals($testResult, $response["result"][0]);
        $this->assertEquals(1, count($response["result"]));
    }

    public function testGetZonesReturnMagentoZoneReturnsInactiveZoneIfItDoesntExists()
    {
        $this->mockClientAPIClient->method('callAPI')->willReturn(
            array(
                "success" => true,
                "result" => array()
            )
        );
        $this->mockClientAPIClient->method('responseOk')->willReturn(true);

        $response = $this->clientActions->getZonesReturnMagentoZone();

        $this->assertEquals("inactive", $response["result"][0]["status"]);
    }

    public function testGetZoneReturnsMagentoZoneReturnsCorrectDomainForListsWithSimilarDomains()
    {
        $expectedDomain = 'domaindomain.com';
        $domain = array('name' => $expectedDomain);
        $domain2 = array('name' => 'domain.com');

        $this->mockMagentoAPI->method('getMagentoDomainName')->willReturn($expectedDomain);
        //order is important, less specific match needs to come before the most specific match
        $this->mockClientAPIClient->method('callAPI')->willReturn(
            array(
                "success" => true,
                "result" => array($domain2, $domain)
            )
        );
        $this->mockClientAPIClient->method('responseOk')->willReturn(true);

        $response = $this->clientActions->getZonesReturnMagentoZone();
        $this->assertEquals($expectedDomain, $response['result'][0]['name']);
    }
}
