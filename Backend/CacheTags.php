<?php

namespace CloudFlare\Plugin\Backend;

use \Psr\Log\LoggerInterface;

class CacheTags
{
    const CLOUDFLARE_CACHE_TAG_HEADER = "X-Cache-Tags";

    /**
     * @var \CloudFlare\Plugin\Backend\ClientAPI
     */
    protected $clientAPI;

    /**
     * @var \CloudFlare\Plugin\Backend\DataStore
     */
    protected $dataStore;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(ClientAPI $clientAPI, DataStore $dataStore, LoggerInterface $logger)
    {
        $this->clientAPI = $clientAPI;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
    }

    /**
     * Sets the X-Cache-Tags header(s) on the response
     *
     * @param $response
     * @param $tags
     * @return $response
     */
    public function setCloudFlareCacheTagsResponseHeader($response, $tags)
    {
        //hash cache tags to fit more in each header
        $tags = $this->hashCacheTags($tags);
        $cacheTagHeaderList = $this->get255ByteCacheTagHeaderStringValues($tags);

        /*
         * CloudFlare Cache Tags allow for multiple X-Cache-Tags headers but Magento2's
         * $response->setHeader() doesn't allow for multiple http headers with the same name
         * so we only set the first one in the list
         */
        if (count($cacheTagHeaderList) > 0) {
            $response->setHeader(self::CLOUDFLARE_CACHE_TAG_HEADER, $cacheTagHeaderList[0]);
            $this->logger->debug("CloudFlare header '". self::CLOUDFLARE_CACHE_TAG_HEADER . "' set with value '". $cacheTagHeaderList[0] ."'");

            if (count($cacheTagHeaderList) > 1) {
                $this->logger->debug("Some CloudFlare cache tags were not set because the total length of the list exceeded 255 bytes.");
            }
        }

        return $response;
    }

    /**
     * Generate list of X-Cache-Tag header values which are less than 255 bytes each
     *
     * @param $cacheTagList
     * @return array
     */
    public function get255ByteCacheTagHeaderStringValues($cacheTagList)
    {
        $cacheTagHeaderList = array();
        $cacheTagHeader = "";

        foreach ($cacheTagList as $cacheTag) {
            $cacheTagHeaderEncoding = mb_detect_encoding($cacheTagHeader);
            $cacheTagEncoding = mb_detect_encoding($cacheTag);

            //Is this cache tag larger than 255 bytes?
            if (mb_strlen($cacheTag, $cacheTagEncoding) >= 255) {
                array_push($cacheTagHeaderList, mb_strcut($cacheTag, 1, 255));
            } //Would appending the current cache tag to the cache tag header put it over the 255 byte limit?
            elseif ((mb_strlen($cacheTagHeader, $cacheTagHeaderEncoding) + mb_strlen(",".$cacheTag, $cacheTagEncoding)) > 255) {
                //Start new header
                array_push($cacheTagHeaderList, $cacheTagHeader);
                $cacheTagHeader = $cacheTag;
            } else {
                //Append cache tag to cache tag header
                if ($cacheTagHeader !== "") { //avoid creating headers that start with a comma.
                    $cacheTagHeader = $cacheTagHeader . ",";
                }
                $cacheTagHeader = $cacheTagHeader . $cacheTag;
            }
        }

        array_push($cacheTagHeaderList, $cacheTagHeader);

        return $cacheTagHeaderList;
    }

    /**
     * Purge cache by tag
     *
     * @param $tags
     */
    public function purgeCacheTags(array $tags)
    {
        if (!empty($tags) && $this->dataStore->get(\CF\API\Plugin::SETTING_PLUGIN_SPECIFIC_CACHE_TAG)) {
            $tags = $this->hashCacheTags($tags);
            $this->clientAPI->zonePurgeCacheByTags($tags);
        }
    }

    /**
     * Purge entire cache
     *
     * @return mixed
     */
    public function purgeCache()
    {
        return $this->clientAPI->zonePurgeCache();
    }

    /**
     * Convert cache tags to 3 character hashes so we can fit more in each header
     *
     * @param array $tags
     * @return array
     */
    public function hashCacheTags(array $tags)
    {
        $hashedCacheTags = array();
        foreach ($tags as $tag) {
            array_push($hashedCacheTags, substr(hash('sha256', $tag), 0, 3));
        }

        return $hashedCacheTags;
    }
}
