<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Index;

/**
 * Blog home page view
 */

use BigBridge\ProductImport\Cron\StockImporter;

class Stock extends \Magento\Framework\App\Action\Action
{

	private $stockArray;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        StockImporter $stockArray       
    ) {
        $this->stockArray  = $stockArray;
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
    	$this->stockArray ->execute();
        //$this->_view->loadLayout();
        //$this->_view->renderLayout();
    }

}
