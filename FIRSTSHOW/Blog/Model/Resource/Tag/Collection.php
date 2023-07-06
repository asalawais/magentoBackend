<?php

namespace FIRSTSHOW\Blog\Model\Resource\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('FIRSTSHOW\Blog\Model\Tag', 'FIRSTSHOW\Blog\Model\Resource\Tag');
    }
}
