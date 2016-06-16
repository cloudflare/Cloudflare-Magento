<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CloudFlare\Plugin\Backend\MagentoAPI;

class ProxyTest extends \PHPUnit_Framework_TestCase {

    protected $mockKeyValueFactory;
    protected $mockLogger;
    protected $magentoAPI;

    public function setUp() {
        $this->mockKeyValueFactory = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->magentoAPI = new MagentoAPI($this->mockKeyValueFactory, $this->mockLogger);
    }

    public function testGetValueReturnsNullForBadKey() {
        $key = "key";

        $mockKeyValueModel = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValue')
            ->disableOriginalConstructor()
            ->getMock();
        $mockKeyValueModel->method('load')
            ->with($key, "key")
            ->willReturn(true);
        $mockKeyValueModel->method('getData')->willReturn(array());

        $this->mockKeyValueFactory->method('create')->willReturn($mockKeyValueModel);

        $result = $this->magentoAPI->getValue($key);
        $this->assertNull($result);
    }

    public function testGetValueReturnsCorrectValue() {
        $key = "key";
        $value = "value";

        $mockKeyValueModel = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValue')
            ->disableOriginalConstructor()
            ->getMock();
        $mockKeyValueModel->method('load')
            ->with($key, "key") // "key" is db column name
            ->willReturn(true);
        $mockKeyValueModel->method('getData')->willReturn(array(
            'id' => 'id',
            'key' => $key,
            'value' => $value
        ));

        $this->mockKeyValueFactory->method('create')->willReturn($mockKeyValueModel);

        $result = $this->magentoAPI->getValue($key);
        $this->assertEquals($value, $result);
    }

    //TODO test setValue($key, $value)
}
