<?php

namespace CloudFlare\Plugin\Test\Unit\Observer;

use \CloudFlare\Plugin\Observer\InvalidateCloudFlareObserver;

class InvalidateCloudFlareObserverTest extends \PHPUnit_Framework_TestCase
{
    protected $invalidateCloudFlareObserver;
    protected $mockCacheTags;
    protected $mockConfig;
    protected $mockEvent;
    protected $mockObject;
    protected $mockObserver;

    public function setUp()
    {
        $this->mockCacheTags = $this->getMockBuilder('\CloudFlare\Plugin\Backend\CacheTags')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\PageCache\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockEvent = $this->getMock('Magento\Framework\Event', ['getObject'], [], '', false);

        $this->mockObject = $this->getMockBuilder('\Magento\Framework\DataObject\IdentityInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockObserver = $this->getMockBuilder('\Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidateCloudFlareObserver = new InvalidateCloudFlareObserver($this->mockConfig, $this->mockCacheTags);
    }

    public function testExecuteCallsPurgeCacheTags()
    {
        $this->mockConfig->method('isEnabled')->willReturn(true);
        $this->mockObject->method('getIdentities')->willReturn(array('cacheTagToPurge'));
        $this->mockEvent->method('getObject')->willReturn($this->mockObject);
        $this->mockObserver->method('getEvent')->willReturn($this->mockEvent);
        $this->mockCacheTags->expects($this->once())->method('purgeCacheTags');

        $this->invalidateCloudFlareObserver->execute($this->mockObserver);
    }
}
