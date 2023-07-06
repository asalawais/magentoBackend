<?php

namespace FIRSTSHOW\Blog\Model\Resource\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'comment_id';
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('FIRSTSHOW\Blog\Model\Comment', 'FIRSTSHOW\Blog\Model\Resource\Comment');
        $this->_map['fields']['comment_id'] = 'main_table.comment_id';
    }

    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }
}
