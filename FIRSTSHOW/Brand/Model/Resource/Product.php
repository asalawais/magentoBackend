<?php

namespace FIRSTSHOW\Brand\Model\Resource;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('firstshow_brand_product', 'entity_id');
    }
}
