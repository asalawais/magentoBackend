<?php

namespace Rokanthemes\Instagram\Block;

use Magento\Framework\View\Element\Template;

class Instagram extends Template {
    
    
    protected function _prepareLayout() {
        
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Instagram'));
    }
    

}