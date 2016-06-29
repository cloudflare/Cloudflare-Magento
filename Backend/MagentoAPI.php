<?php
namespace CloudFlare\Plugin\Backend;

use \CF\Integration\IntegrationAPIInterface;
use \CF\DNSRecord;
use \CloudFlare\Plugin\Model\KeyValueFactory;
use \CF\Integration\DataStoreInterface;
use \Psr\Log\LoggerInterface;
use \CloudFlare\Plugin\Setup\InstallSchema;
use \Magento\Store\Model\StoreManagerInterface;

class MagentoAPI implements IntegrationAPIInterface
{
    protected $keyValueFactory;
    protected $logger;
    protected $storeManager;

    /**
     * @param KeyValueFactory $keyValueFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(KeyValueFactory $keyValueFactory, StoreManagerInterface $storeManager, LoggerInterface $logger) {
        $this->keyValueFactory = $keyValueFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function getMagentoDomainName() {

        //getBaseUrl() has format (http | https)://[DOMAIN NAME]/
        //need [DOMAIN NAME]
        $domainName = $this->storeManager->getStore()->getBaseUrl();
        $domainName = str_replace("http://", "", $domainName);
        $domainName = str_replace("https://", "", $domainName);
        $domainName = rtrim($domainName, "/");

        return $domainName;
    }

    /**
     * @param $key
     * @return null
     */
    public function getValue($key) {
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
    public function setValue($key, $value) {
        $keyValueModel = $this->keyValueFactory->create();
        $keyValueModel->load($key, InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN);
        if(empty($keyValueModel->getData())) {
            //key doesn't exist yet, create new
            $keyValueModel = $this->keyValueFactory->create();
            $keyValueModel->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_KEY_COLUMN, $key);
            $keyValueModel->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value);
            $keyValueModel->save();
        } else {
            //update existing key
            $keyValueModel->setData(InstallSchema::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN, $value);
            $keyValueModel->save();
        }
    }

    /**
     * @param $domainName
     * @return mixed
     */
    public function getDNSRecords($domainName) {
        return null;
    }

    /**
     * @param $domainName
     * @param DNSRecord $DNSRecord
     * @return mixed
     */
    public function addDNSRecord($domainName, DNSRecord $DNSRecord) {
        return null;
    }

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     * @return mixed
     */
    public function editDNSRecord($domain_name, DNSRecord $DNSRecord) {
        return null;
    }

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     * @return mixed
     */
    public function removeDNSRecord($domain_name, DNSRecord $DNSRecord) {
        return null;
    }

    /**
     * @return mixed
     */
    public function getHostAPIKey() {
        return null;
    }

    /**
     * @param null $userId
     * @return mixed
     */
    public function getDomainList($userId = null) {
        return null;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return null;
    }
}
