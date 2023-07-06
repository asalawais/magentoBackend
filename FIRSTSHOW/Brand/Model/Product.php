<?php

namespace FIRSTSHOW\Brand\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('FIRSTSHOW\Brand\Model\Resource\Product');
    }
}
