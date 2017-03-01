<?php
namespace CloudFlare\Plugin\Test\Unit\Model\Layout;

use CloudFlare\Plugin\Model\Layout\LayoutPlugin;

class LayoutPluginTest extends \PHPUnit_Framework_TestCase
{
    protected $layoutPlugin;
    protected $mockCacheTags;
    protected $mockConfig;
    protected $mockLogger;
    protected $mockResponse;

    public function setUp()
    {
        $this->mockCacheTags = $this->getMockBuilder('\CloudFlare\Plugin\Backend\CacheTags')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockResponse = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutPlugin = new LayoutPlugin(
            $this->mockResponse,
            $this->mockConfig,
            $this->mockLogger,
            $this->mockCacheTags
        );
    }

    public function testLayoutPluginCallsSetCloudFlareCacheTagsResponseHeaderOnce()
    {
        $mockSubject = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSubject->method('isCacheable')->willReturn(true);
        $mockSubject->method('getAllBlocks')->willReturn(array());
        $this->mockConfig->method('isEnabled')->willReturn(true);
        $this->mockCacheTags->expects($this->once())
            ->method('setCloudFlareCacheTagsResponseHeader');

        $this->layoutPlugin->afterGetOutput($mockSubject, null);
    }
}
