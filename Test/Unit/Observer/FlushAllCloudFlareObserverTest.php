<?php

namespace CloudFlare\Plugin\Test\Unit\Observer;

use \CloudFlare\Plugin\Observer\FlushAllCloudFlareObserver;

class FlushAllCloudFlareObserverTest extends \PHPUnit_Framework_TestCase
{
    protected $flushAllCloudFlareObserver;
    protected $mockCacheTags;
    protected $mockConfig;
    protected $mockObserver;

    public function setUp()
    {
        $this->mockCacheTags = $this->getMockBuilder('\CloudFlare\Plugin\Backend\CacheTags')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockObserver = $this->getMockBuilder('\Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->flushAllCloudFlareObserver = new FlushAllCloudFlareObserver($this->mockConfig, $this->mockCacheTags);
    }

    public function testExecuteCallsPurgeCache()
    {
        $this->mockConfig->method('isEnabled')->willReturn(true);
        $this->mockCacheTags->expects($this->once())->method('purgeCache');

        $this->flushAllCloudFlareObserver->execute($this->mockObserver);
    }
}
