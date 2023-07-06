<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Portfolio\Controller\Adminhtml;
abstract class Portfolio extends \Magento\Backend\App\Action
{
	/**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'FIRSTSHOW_Portfolio::portfolio_manage'
        )->_addBreadcrumb(
            __('Portfolio'),
            __('Portfolio')
        );
        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FIRSTSHOW_Portfolio::portfolio');
    }
}
