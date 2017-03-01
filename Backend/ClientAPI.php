<?php
namespace CloudFlare\Plugin\Backend;

use \CF\API\Client;
use \CF\API\Request;

class ClientAPI extends Client
{

    /**
     * DELETE zones/:id/purge_cache
     * https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
     *
     * @param $tags
     * @return
     */
    public function zonePurgeCacheByTags($tags)
    {
        $zoneId = $this->getZoneIdForDomainName($this->integrationAPI->getMagentoDomainName());

        $request = new Request('DELETE', 'zones/'. $zoneId .'/purge_cache', array(), array('tags' => $tags));

        return $this->callAPI($request);
    }

    /**
     * DELETE zones/:id/purge_cache
     * https://api.cloudflare.com/#zone-purge-all-files
     *
     * @return mixed
     */
    public function zonePurgeCache()
    {
        $zoneId = $this->getZoneIdForDomainName($this->integrationAPI->getMagentoDomainName());

        $request = new Request('DELETE', 'zones/'. $zoneId .'/purge_cache', array(), array('purge_everything' => true));

        return $this->callAPI($request);
    }

    /**
     * @param $domainName
     * @return null
     */
    protected function getZoneIdForDomainName($domainName)
    {
        $zoneId = $this->data_store->getZoneId($domainName);
        if ($zoneId !== null) {
            return $zoneId;
        }

        $request = new Request('GET', 'zones', array('name' => $domainName), array());
        $response = $this->callAPI($request);
        if ($this->responseOk($response) && count($response['result']) > 0) {
            $zoneId = $response['result'][0]['id'];
            $this->data_store->setZoneId($domainName, $zoneId);
            return $zoneId;
        }

        return null;
    }
}
