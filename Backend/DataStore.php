<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\DataStoreInterface;
use \Psr\Log\LoggerInterface;
use \CloudFlare\Plugin\Model\KeyValueFactory;
use \CloudFlare\Plugin\Setup\InstallSchema;

class DataStore implements DataStoreInterface
{
    protected $keyValueFactory;
    protected $logger;

    /**
     * @param KeyValueFactory $keyValueFactory
     * @param LoggerInterface $logger
     */
    public function __construct(KeyValueFactory $keyValueFactory, LoggerInterface $logger) {
        $this->keyValueFactory = $keyValueFactory;
        $this->logger = $logger;
    }

    /**
     * @param $key
     * @return null
     */
    private function getValue($key) {
        $keyValueModel = $this->keyValueFactory->create();
        $keyValueModel->load($key, InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN);
        $result = $keyValueModel->getData();

        if(array_key_exists(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $result)) {
            return $result[InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN];
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     */
    private function setValue($key, $value) {
        $keyValueModel = $this->keyValueFactory->create();
        $keyValueModel->load($key, InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN);
        if(empty($keyValueModel->getData())) {
            //key doesn't exist yet, create new
            $keyValueModel = $this->keyValueFactory->create();
            $keyValueModel->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN, $key)
                ->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value)
                ->save();
        } else {
            //update existing key
            $keyValueModel->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value)
                ->save();
        }
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
        return "3ee5bf2786006ca7494efe1be1810c2b736a8";
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
        return "john.wineman@gmail.com";
    }
}
