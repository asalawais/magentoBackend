<?php

namespace FIRSTSHOW\Blog\Controller\Adminhtml\Category;

class NewAction extends \FIRSTSHOW\Blog\Controller\Adminhtml\Blog
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FIRSTSHOW_Blog::edit_category');
    }
}
