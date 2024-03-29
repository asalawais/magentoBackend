<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Testimonial\Controller\Adminhtml;
abstract class Testimonial extends \Magento\Backend\App\Action
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
            'FIRSTSHOW_Testimonial::testimonial_manage'
        )->_addBreadcrumb(
            __('Testimonial'),
            __('Testimonial')
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
        return $this->_authorization->isAllowed('FIRSTSHOW_Testimonial::testimonial');
    }
}
