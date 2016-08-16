<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use \CF\Integration\DefaultIntegration;
use \CloudFlare\Plugin\Backend\ClientActions;
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

        $this->mockIntegrationContext = new \CF\Integration\DefaultIntegration($this->mockConfig, $this->mockMagentoAPI, $this->mockDataStore, $this->mockLogger);
        $this->clientActions = new ClientActions($this->mockIntegrationContext, $this->mockClientAPIClient, $this->mockRequest);
    }

    public function testGetZonesReturnMagentoZoneReturnsZoneIfItExists() {
        $testResult = "testResult";
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

    public function testGetZonesReturnMagentoZoneReturnsInactiveZoneIfItDoesntExists() {
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

    public function testGetZonesReturnMagentoZoneAddsNameParameter() {
        $domainName = "domainName";

        $this->mockMagentoAPI->method('getMagentoDomainName')->willReturn($domainName);

        $this->mockClientAPIClient->expects($this->once())
            ->method('callAPI')
            ->with(new Request(null, null, array("name" => $domainName), null));

        $this->clientActions = new ClientActions($this->mockIntegrationContext, $this->mockClientAPIClient, new Request(null, null, null, null));

        $this->clientActions->getZonesReturnMagentoZone();
    }
}