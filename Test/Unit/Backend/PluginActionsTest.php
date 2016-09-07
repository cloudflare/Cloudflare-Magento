<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CF\API\Exception\PageRuleLimitException;
use \CF\API\Exception\ZoneSettingFailException;
use \CF\Integration\DefaultIntegration;
use \CloudFlare\Plugin\Backend\PluginActions;

class PluginActionsTest extends \PHPUnit_Framework_TestCase
{

    protected $mockConfig;
    protected $mockClientAPIClient;
    protected $mockDataStore;
    protected $mockLogger;
    protected $mockIntegrationContext;
    protected $mockMagentoAPI;
    protected $mockPluginAPIClient;
    protected $mockRequest;
    protected $pluginActions;


    public function setUp()
    {
        $this->mockConfig = $this->getMockBuilder('\CF\Integration\DefaultConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockClientAPIClient = $this->getMockBuilder('\CF\API\Client')
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
        $this->mockPluginAPIClient = $this->getMockBuilder('\CF\API\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockRequest = $this->getMockBuilder('\CF\API\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockIntegrationContext = new DefaultIntegration($this->mockConfig, $this->mockMagentoAPI, $this->mockDataStore, $this->mockLogger);
        $this->pluginActions = new PluginActions($this->mockIntegrationContext, $this->mockPluginAPIClient, $this->mockRequest);
        $this->pluginActions->setClientAPI($this->mockClientAPIClient);
    }

    public function testPatchZoneSettingThrowsExceptionForBadResponse() {
        $this->setExpectedException(ZoneSettingFailException::class);
        $this->mockClientAPIClient->method('callAPI')->willReturn(array('errors' => array()));
        $this->pluginActions->patchZoneSetting(null, null, null);
    }

    public function testPatchZoneSettingWillReturnTrueForPlanUpgradeError() {
        $this->mockClientAPIClient->method('callAPI')->willReturn(array(
            'errors' => array(
                array(
                    'code' => '',
                    'message' => 'Not allowed to edit setting for polish'
                )
            )
        ));
        $this->assertTrue($this->pluginActions->patchZoneSetting(null, null, null));
    }

    public function testPostPageRuleThrowsExceptionForBadResponse() {
        $this->setExpectedException(PageRuleLimitException::class);
        $this->mockClientAPIClient->method('callAPI')->willReturn(array('errors' => array()));
        $this->pluginActions->postPageRule(null, null, null);
    }
}