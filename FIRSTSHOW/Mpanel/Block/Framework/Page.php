<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Mpanel\Block\Framework;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Page extends \Magento\Framework\View\Result\Page
{
	/**
     * Add default body classes for current page layout
     *
     * @return $this
     */
    protected function addDefaultBodyClasses()
    {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
        }
		$width = $this->getStoreConfig('firstshowtheme/general/width');
		if($width != 'width1200'){
			$this->pageConfig->addBodyClass($width);
		}
		
        return $this;
    }
	
	public function getStoreConfig($node){
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('FIRSTSHOW\Mpanel\Helper\Data');
		
		return $helper->getStoreConfig($node);
	}
}

