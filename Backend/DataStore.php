<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DataStoreInterface;

class DataStore implements DataStoreInterface
{
    protected $magentoAPI;

    const CLIENT_API_KEY = "clientApiKey";
    const CLOUDFLARE_EMAIL = "cloudflareEmail";

    public function __construct(MagentoAPI $magentoAPI) {
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
    public function createUserDataStore($clientAPIKey, $email, $uniqueId, $userKey) {
        //Magento doesn't use the host api - $uniqueId, $userKey will always be null
        $this->magentoAPI->setValue(self::CLIENT_API_KEY, $clientAPIKey);
        $this->magentoAPI->setValue(self::CLOUDFLARE_EMAIL, $email);
    }

    /**
     * @return mixed
     */
    public function getHostAPIUserUniqueId() {
        return null;
    }

    /**
     * @return mixed
     */
    public function getClientV4APIKey() {
        return $this->magentoAPI->getValue(self::CLIENT_API_KEY);
    }

    /**
     * @return mixed
     */
    public function getHostAPIUserKey() {
        return null;
    }

    /**
     * @return mixed
     */
    public function getCloudFlareEmail() {
        return $this->magentoAPI->getValue(self::CLOUDFLARE_EMAIL);
    }
}
