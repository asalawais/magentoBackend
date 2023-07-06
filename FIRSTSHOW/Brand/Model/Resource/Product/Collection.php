<?php

namespace FIRSTSHOW\Brand\Model\Resource\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('FIRSTSHOW\Brand\Model\Product', 'FIRSTSHOW\Brand\Model\Resource\Product');
    }
}
