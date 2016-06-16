<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DataStoreInterface;

class DataStore implements DataStoreInterface
{
    protected $magentoAPI;

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
        //TODO
        return true;
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
        //TODO
        return "";
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
        //TODO
        return "";
    }
}
