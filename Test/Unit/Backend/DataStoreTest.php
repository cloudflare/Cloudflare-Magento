<?php
namespace CloudFlare\Plugin\Test\Unit\Backend;

use CloudFlare\Plugin\Backend\DataStore;

class DataStoreTest extends \PHPUnit_Framework_TestCase
{

    protected $dataStore;
    protected $mockMagentoAPI;

    public function setUp()
    {
        $this->mockMagentoAPI = $this->getMockBuilder('\CloudFlare\Plugin\Backend\MagentoAPI')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataStore = new DataStore($this->mockMagentoAPI);
    }

    public function testCreateUserDataStoreSavesAPIKeyAndEmail()
    {
        $apiKey = "apiKey";
        $email = "email";

        $this->mockMagentoAPI->expects($this->at(0))
            ->method('setValue')
            ->with(DataStore::CLIENT_API_KEY, json_encode($apiKey));

        $this->mockMagentoAPI->expects($this->at(1))
            ->method('setValue')
            ->with(DataStore::CLOUDFLARE_EMAIL, json_encode($email));

        $this->dataStore->createUserDataStore($apiKey, $email, null, null);
    }

    public function testGetClientV4APIKeyReturnsCorrectValue()
    {
        $apiKey = "apiKey";
        $this->mockMagentoAPI->method('getValue')->willReturn(json_encode($apiKey));

        $response = $this->dataStore->getClientV4APIKey();
        $this->assertEquals($response, $apiKey);
    }

    public function testGetCloudFlareEmailReturnsCorrectValue()
    {
        $email = "email";
        $this->mockMagentoAPI->method('getValue')->willReturn(json_encode($email));

        $response = $this->dataStore->getClientV4APIKey();
        $this->assertEquals($response, $email);
    }

    public function testGetHostAPIUserKeyReturnsNull()
    {
        $this->assertNull($this->dataStore->getHostAPIUserKey());
    }

    public function testGetHostAPIUserUniqueIdReturnsNull()
    {
        $this->assertNull($this->dataStore->getHostAPIUserUniqueId());
    }

    public function testGetZoneIdReturnsValue()
    {
        $domain = "domain";
        $key = DataStore::ZONE_ID_KEY.$domain;
        $zoneId = "zoneId";

        $this->mockMagentoAPI->method('getValue')->with($key)->willReturn(json_encode($zoneId));
        $response = $this->dataStore->getZoneId($domain);
        $this->assertEquals($zoneId, $response);
    }

    public function testSetZoneIdSetsValue()
    {
        $domain = "domain";
        $key = DataStore::ZONE_ID_KEY.$domain;
        $zoneId = "zoneId";

        $this->mockMagentoAPI->expects($this->once())->method('setValue')->with($key, json_encode($zoneId));
        $this->dataStore->setZoneId($domain, $zoneId);
    }

    public function testGetCallsMagentoAPIGetValue()
    {
        $key = "key";
        $this->mockMagentoAPI->expects($this->once())->method('getValue')->with($key);
        $this->dataStore->get($key);
    }

    public function testSetCallsMagentoAPISetValue()
    {
        $key = "key";
        $value = "value";
        $this->mockMagentoAPI->expects($this->once())->method('setValue')->with($key, json_encode($value));
        $this->dataStore->set($key, $value);
    }
}
