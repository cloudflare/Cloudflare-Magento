<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DataStoreInterface;

class DataStore implements DataStoreInterface
{
    protected $magentoAPI;

    const CLIENT_API_KEY = "clientApiKey";
    const CLOUDFLARE_EMAIL = "cloudflareEmail";
    const ZONE_ID_KEY = "zoneId:";

    public function __construct(MagentoAPI $magentoAPI)
    {
        $this->magentoAPI = $magentoAPI;
    }

    /**
     * @param $clientAPIKey
     * @param $email
     * @param $uniqueId
     * @param $userKey
     * @return mixed
     * @internal param $client_api_key
     * @internal param $unique_id
     * @internal param $user_key
     */
    public function createUserDataStore($clientAPIKey, $email, $uniqueId, $userKey)
    {
        //Magento doesn't use the host api - $uniqueId, $userKey will always be null
        $this->set(self::CLIENT_API_KEY, $clientAPIKey);
        $this->set(self::CLOUDFLARE_EMAIL, $email);
        return true;
    }

    /**
     * @return mixed
     */
    public function getHostAPIUserUniqueId()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getClientV4APIKey()
    {
        return $this->get(self::CLIENT_API_KEY);
    }

    /**
     * @return mixed
     */
    public function getHostAPIUserKey()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getCloudFlareEmail()
    {
        return $this->get(self::CLOUDFLARE_EMAIL);
    }

    /**
     * @param $domainName
     * @return null
     */
    public function getZoneId($domainName)
    {
        return $this->get(self::ZONE_ID_KEY . $domainName);
    }

    /**
     * @param $domainName
     * @param $zoneId
     * @return mixed
     */
    public function setZoneId($domainName, $zoneId)
    {
        return $this->set(self::ZONE_ID_KEY . $domainName, $zoneId);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return json_decode($this->magentoAPI->getValue($key), true);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->magentoAPI->setValue($key, json_encode($value));
    }
}
