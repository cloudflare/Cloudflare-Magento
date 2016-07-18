<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use \CF\Integration\DefaultIntegration;
use \CloudFlare\Plugin\Backend\PluginActions;

class PluginActionsTest extends \PHPUnit_Framework_TestCase
{
    protected $mockConfig;
    protected $mockDataStore;
    protected $mockIntegrationContext;
    protected $mockLogger;
    protected $mockMagentoAPI;
    protected $mockPluginAPIClient;
    protected $mockRequest;
    protected $pluginActions;

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
        $this->mockPluginAPIClient = $this->getMockBuilder('\CF\API\Plugin')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockRequest = $this->getMockBuilder('\CF\API\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockIntegrationContext = new \CF\Integration\DefaultIntegration($this->mockConfig, $this->mockMagentoAPI, $this->mockDataStore, $this->mockLogger);
        $this->pluginActions = new PluginActions($this->mockIntegrationContext, $this->mockPluginAPIClient, $this->mockRequest);
    }

    public function testPostAccountSaveAPICredentialsReturnsErrorIfMissingApiKey() {
        $this->mockRequest->method('getBody')->willReturn(array(
           'email' => 'email'
        ));
        $this->mockPluginAPIClient->method('createAPIError')->willReturn(array('success' => false));

        $response = $this->pluginActions->postAccountSaveAPICredentials();

        $this->assertFalse($response['success']);
    }

    public function testPostAccountSaveAPICredentialsReturnsErrorIfMissingEmail() {
        $this->mockRequest->method('getBody')->willReturn(array(
            'apiKey' => 'apiKey'
        ));
        $this->mockPluginAPIClient->method('createAPIError')->willReturn(array('success' => false));

        $response = $this->pluginActions->postAccountSaveAPICredentials();

        $this->assertFalse($response['success']);
    }


    public function testPostAccountSaveAPICredentialsReturnsDataStoreEmailIfSuccessful() {
        $email = "email";
        $this->mockRequest->method('getBody')->willReturn(array(
            'apiKey' => 'apiKey',
            $email => $email
        ));
        $this->mockPluginAPIClient->method('createAPISuccessResponse')->willReturn(array('email' => $email));
        $this->mockDataStore->expects($this->once())
            ->method('getCloudFlareEmail');

        $this->pluginActions->postAccountSaveAPICredentials();
    }

    public function testGetPluginSettingsReturnsArray() {
        $this->mockPluginAPIClient
            ->expects($this->once())
            ->method('createAPISuccessResponse')
            ->will($this->returnCallback(function($input) {
                $this->assertTrue(is_array($input));
            }));
        $this->pluginActions->getPluginSettings();
    }

    public function testPatchPluginSettingsReturnsErrorForBadSetting() {
        $this->mockRequest->method('getUrl')->willReturn('plugin/:id/settings/nonExistentSetting');
        $this->mockPluginAPIClient->expects($this->once())->method('createAPIError');
        $this->pluginActions->patchPluginSettings();
    }
}