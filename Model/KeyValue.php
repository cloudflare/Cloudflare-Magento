<?php

namespace CloudFlare\Plugin\Model;

use Magento\Framework\Model\AbstractModel;

class KeyValue extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('CloudFlare\Plugin\Model\ResourceModel\KeyValue');
    }
}
