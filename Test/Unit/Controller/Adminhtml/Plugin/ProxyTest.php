<?php
namespace CloudFlare\Plugin\Test\Unit\Controller\Adminhtml\Plugin;

use CloudFlare\Plugin\Controller\Adminhtml\Plugin\Proxy;

class ProxyTest extends \PHPUnit_Framework_TestCase {
    private $mockContext;
    private $mockLogger;
    private $mockResultJsonFactory;
    private $proxy;

    public function setUp() {
        $this->mockContext = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockResultJsonFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->proxy = new Proxy($this->mockContext, $this->mockResultJsonFactory, $this->mockLogger);
    }

    public function testProcessUrlKeysCallsSetJsonFormTokenOnMagentoRequest() {
        $mockAuth = $this->getMockBuilder('\Magento\Backend\Model\Auth')
            ->disableOriginalConstructor()
            ->getMock();
        $mockAuth->method('isLoggedIn')->willReturn(false);
        $this->mockContext->method('getAuth')->willReturn($mockAuth);

        $mockProxy = $this->getMock('CloudFlare\Plugin\Controller\Adminhtml\Plugin\Proxy',
            array('setJsonFormTokenOnMagentoRequest', 'getJSONBody'),
            array($this->mockContext, $this->mockResultJsonFactory, $this->mockLogger)
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
}