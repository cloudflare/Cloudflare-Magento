<?php
namespace CloudFlare\Plugin\Controller\Hello;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class World extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        echo '<p>You Did It!</pd>';
        var_dump(__METHOD__);
    }
}