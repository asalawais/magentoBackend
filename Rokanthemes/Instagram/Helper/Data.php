<?php

namespace Rokanthemes\Instagram\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    public function getConfig($key)
    {
        $result = $this->scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $result;
    }
}
