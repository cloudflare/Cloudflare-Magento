<?php

namespace CloudFlare\Plugin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use CloudFlare\Plugin\Setup\InstallSchema;

class KeyValue extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::CLOUDFLARE_DATA_TABLE_NAME, InstallSchema::CLOUDFLARE_DATA_TABLE_ID_COLUMN);
    }
}
