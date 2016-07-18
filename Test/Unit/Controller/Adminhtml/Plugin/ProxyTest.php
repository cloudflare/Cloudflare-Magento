<?php
namespace CloudFlare\Plugin\Test\Unit\Controller\Adminhtml\Plugin;

use CloudFlare\Plugin\Controller\Adminhtml\Plugin\Proxy;

class ProxyTest extends \PHPUnit_Framework_TestCase {
    private $mockConfigReader;
    private $mockContext;
    private $mockLogger;
    private $mockKeyValueFactory;
    private $mockResultJsonFactory;
    private $mockStoreManager;
    private $proxy;

    public function setUp() {
        $this->mockConfigReader = $this->getMockBuilder('\Magento\Framework\App\DeploymentConfig\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockContext = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockKeyValueFactory = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockStoreManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockResultJsonFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->proxy = new Proxy($this->mockContext, $this->mockResultJsonFactory, $this->mockLogger, $this->mockKeyValueFactory, $this->mockStoreManager, $this->mockConfigReader);
    }

    public function testProcessUrlKeysCallsSetJsonFormTokenOnMagentoRequest() {
        $mockAuth = $this->getMockBuilder('\Magento\Backend\Model\Auth')
            ->disableOriginalConstructor()
            ->getMock();
        $mockAuth->method('isLoggedIn')->willReturn(false);
        $this->mockContext->method('getAuth')->willReturn($mockAuth);

        $mockProxy = $this->getMock('CloudFlare\Plugin\Controller\Adminhtml\Plugin\Proxy',
            array('setJsonFormTokenOnMagentoRequest', 'getJSONBody'),
            array($this->mockContext, $this->mockResultJsonFactory, $this->mockLogger, $this->mockKeyValueFactory, $this->mockStoreManager, $this->mockConfigReader)
        );
        $mockProxy->method('getJSONBody')->willReturn(array(Proxy::FORM_KEY => Proxy::FORM_KEY));

        $mockProxy->expects($this->once())
            ->method('setJsonFormTokenOnMagentoRequest');

        $mockProxy->_processUrlKeys();
    }

    public function testSetJsonFormTokenOnMagentoRequestSetsTokenCorrectly() {
        $token = "token";

        $mockCookieInterface = $this->getMockBuilder('\Magento\Framework\Stdlib\Cookie\CookieReaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStringUtils = $this->getMockBuilder('\Magento\Framework\Stdlib\StringUtils')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRequest = new \Magento\Framework\HTTP\PhpEnvironment\Request($mockCookieInterface, $mockStringUtils, null);

        $this->proxy->setJsonFormTokenOnMagentoRequest($token, $mockRequest);


        $this->assertEquals($token, $mockRequest->getParam(Proxy::FORM_KEY));
    }

    public function testIsClientAPIReturnsTrueForClientAPIPaths() {
        $this->assertTrue($this->proxy->isClientAPI("https://api.cloudflare.com/client/v4/zones/:zoneId"));
    }

    public function testIsPluginAPIReturnsTrueForPluginAPIPaths() {
        $this->assertTrue($this->proxy->isPluginAPI("https://partners.cloudflare/plugins/account/"));
    }

}