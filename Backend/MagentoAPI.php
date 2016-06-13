<?php
namespace CloudFlare\Plugin\Backend;
use \CF\Integration\IntegrationAPIInterface;
use \CF\Integration\LoggerInterface;
use \CF\DNSRecord;

class MagentoAPI implements IntegrationAPIInterface
{
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
