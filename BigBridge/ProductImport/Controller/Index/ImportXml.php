<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Index;

/**
 * Blog home page view
 */

use BigBridge\ProductImport\Cron\ProductImportXml;

class ImportXml extends \Magento\Framework\App\Action\Action
{

	private $productsArray;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        ProductImportXml $productsArray       
    ) {
        $this->productsArray = $productsArray;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	//die('sdfsd');
    	$this->productsArray->execute();
        //$this->_view->loadLayout();
        //$this->_view->renderLayout();
    }

}
