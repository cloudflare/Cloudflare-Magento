<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CF\API\Plugin;
use CloudFlare\Plugin\Backend\CacheTags;

class CacheTagsTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheTags;
    protected $mockClientAPI;
    protected $mockDataStore;
    protected $mockLogger;

    public function setUp()
    {
        $this->mockClientAPI = $this->getMockBuilder('\CloudFlare\Plugin\Backend\ClientAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataStore = $this->getMockBuilder('\CloudFlare\Plugin\Backend\DataStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLogger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheTags = new CacheTags($this->mockClientAPI, $this->mockDataStore, $this->mockLogger);
    }

    public function testSetCloudFlareCacheTagsResponseHeaderSetsHeader()
    {
        $mockResponse = $this->getMockBuilder('\Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResponse->expects($this->once())->method('setHeader');
        $tags = array("tags");
        $this->cacheTags->setCloudFlareCacheTagsResponseHeader($mockResponse, $tags);
    }

    public function testGet255ByteCacheTagHeaderStringValuesReturnsArraySizeOneForInputLessThan255Bytes()
    {
        $tag = $this->generateByteString(1);
        $tags = array($tag);
        $response = $this->cacheTags->get255ByteCacheTagHeaderStringValues($tags);
        $this->assertEquals(1, count($response));
        $this->assertEquals($tag, $response[0]);
    }

    public function testGet255ByteCacheTagHeaderStringValuesConcatsMultipleTags()
    {
        $tag1 = $this->generateByteString(1);
        $tag2 = $this->generateByteString(2);
        $tags = array($tag1, $tag2);
        $response = $this->cacheTags->get255ByteCacheTagHeaderStringValues($tags);
        $this->assertEquals($tag1.",".$tag2, $response[0]);
    }

    public function testGet255ByteCacheTagHeaderStringValuesTrimsLargeTags()
    {
        $tag256 = $this->generateByteString(256);
        $this->assertEquals(256, mb_strlen($tag256, mb_detect_encoding($tag256)));

        $response = $this->cacheTags->get255ByteCacheTagHeaderStringValues(array($tag256));
        $this->assertEquals(255, mb_strlen($response[0], mb_detect_encoding($response[0])));
    }

    public function testGet255ByteCacheTagHeaderStringValuesHandlesTagListsLargerThan255Bytes()
    {
        $tag255 = $this->generateByteString(255);
        $tag1 = $this->generateByteString(1);
        $tags = array($tag255, $tag1);
        $response = $this->cacheTags->get255ByteCacheTagHeaderStringValues($tags);
        $this->assertEquals(2, count($response));
    }

    protected function generateByteString($length)
    {
        $string = "";
        for ($i = 1; $i<=$length; $i++) {
            $string = $string."a";
        }
        return $string;
    }

    public function testPurgeCacheTagsDoesntCallAPIForEmptyArray()
    {
        $this->mockDataStore->method('get')->willReturn(false);
        $tags = array();
        $this->mockClientAPI->expects($this->never())->method('zonePurgeCacheByTags');
        $this->cacheTags->purgeCacheTags($tags);
    }

    public function testPurgeCacheTagsDoesntCallAPIIfAutomaticCacheTagDisabled()
    {
        $this->mockDataStore->method('get')->with(Plugin::SETTING_PLUGIN_SPECIFIC_CACHE_TAG)->willReturn(false);
        $tags = array('tagsToPurge');
        $this->mockClientAPI->expects($this->never())->method('zonePurgeCacheByTags');
        $this->cacheTags->purgeCacheTags($tags);
    }

    public function testPurgeCacheTagsCallsAPIForNonEmptyArray()
    {
        $this->mockDataStore->method('get')->with(Plugin::SETTING_PLUGIN_SPECIFIC_CACHE_TAG)->willReturn(true);
        $tags = array('tagToPurge');
        $this->mockClientAPI->expects($this->once())->method('zonePurgeCacheByTags');
        $this->cacheTags->purgeCacheTags($tags);
    }

    public function testHashCacheTagsHashesWithSha256AndTruncatesToThreeCharacters()
    {
        $tag = "cacheTag";
        $tags = array($tag);
        $expectedHash = substr(hash('sha256', $tag), 0, 3);
        $hashedCacheTags = $this->cacheTags->hashCacheTags($tags);
        $this->assertEquals($expectedHash, $hashedCacheTags[0]);
    }

    public function testPurgeCacheCallsPurgeCacheAPI()
    {
        $this->mockClientAPI->expects($this->once())->method('zonePurgeCache');
        $this->cacheTags->purgeCache();
    }
}
