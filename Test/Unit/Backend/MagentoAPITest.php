<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CloudFlare\Plugin\Backend\MagentoAPI;
use CloudFlare\Plugin\Setup\InstallSchema;

class MagentoAPITest extends \PHPUnit_Framework_TestCase {

    protected $mockKeyValueFactory;
    protected $mockKeyValueModel;
    protected $mockLogger;
    protected $magentoAPI;

    public function setUp() {
        $this->mockKeyValueFactory = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockKeyValueModel = $this->getMockBuilder('\CloudFlare\Plugin\Model\KeyValue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockKeyValueFactory->method('create')->willReturn($this->mockKeyValueModel);
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->magentoAPI = new MagentoAPI($this->mockKeyValueFactory, $this->mockLogger);
    }

    public function testGetValueReturnsNullForBadKey() {
        $key = "key";

        $this->mockKeyValueModel->method('load')
            ->with($key, "key")
            ->willReturn(true);
        $this->mockKeyValueModel->method('getData')->willReturn(array());

        $result = $this->magentoAPI->getValue($key);
        $this->assertNull($result);
    }

    public function testGetValueReturnsCorrectValue() {
        $key = "key";
        $value = "value";

        $this->mockKeyValueModel->method('load')
            ->with($key, InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN)
            ->willReturn(true);
        $this->mockKeyValueModel->method('getData')->willReturn(array(
            'id' => 'id',
            'key' => $key,
            'value' => $value
        ));

        $result = $this->magentoAPI->getValue($key);
        $this->assertEquals($value, $result);
    }

    public function testSetValueCreatesNewKeyIfItDoesntExist() {
        $key = "key";
        $value = "value";

        $this->mockKeyValueModel->method('getData')->willReturn(array());

        $this->mockKeyValueModel->expects($this->at(1))
            ->method('create');
        $this->mockKeyValueModel->expects($this->at(2))
            ->method('setData')
            ->with(InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN, $key);
        $this->mockKeyValueModel->expects($this->at(3))
            ->method('setData')
            ->with(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value);
        $this->mockKeyValueModel->expects($this->once())->method('save');

        $this->magentoAPI->setValue($key, $value);

    }

    public function testSetValueUpdatesExistingKey() {
        $id = "1";
        $key = "key";
        $value = "value";

        $this->mockKeyValueModel->method('getData')->willReturn(array(
            $id => $id,
            $key => $key,
            $value => $value
        ));

        $this->mockKeyValueModel->expects($this->at(1))
            ->method('create');
        $this->mockKeyValueModel->expects($this->once())
            ->method('setData')
            ->with(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value);
        $this->mockKeyValueModel->expects($this->once())->method('save');

        $this->magentoAPI->setValue($key, $value);
    }
}
