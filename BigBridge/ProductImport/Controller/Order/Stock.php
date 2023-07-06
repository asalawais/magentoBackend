<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Order;

/**
 * Blog home page view
 */

use BigBridge\ProductImport\Cron\Orderpush;
use BigBridge\ProductImport\Cron\StockImporter;

class Stock extends \Magento\Framework\App\Action\Action
{

	private $stock;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        StockImporter $stock
    ) {
        $this->stock = $stock;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	$this->stock->execute();
    }

}
