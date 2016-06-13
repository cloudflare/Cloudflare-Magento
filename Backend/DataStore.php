<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DataStoreInterface;

class DataStore implements DataStoreInterface
{
    /**
     * @param $client_api_key
     * @param $email
     * @param $unique_id
     * @param $user_key
     * @return mixed
     */
    public function createUserDataStore($client_api_key, $email, $unique_id, $user_key) {
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
