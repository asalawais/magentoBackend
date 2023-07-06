<?php

namespace FIRSTSHOW\Brand\Controller\Adminhtml\Brand;

class NewAction extends \FIRSTSHOW\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FIRSTSHOW_Brand::edit_brand');
    }
}
