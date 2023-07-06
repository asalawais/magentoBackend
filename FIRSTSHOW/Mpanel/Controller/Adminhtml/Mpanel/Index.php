<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel;

use Magento\Backend\App\Action;

class Index extends \FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Mpanel'));
        $this->_view->renderLayout();
    }
}
