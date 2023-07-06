<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Brouwer;

/**
 * Blog home page view
 */

use BigBridge\ProductImport\Cron\BrouwersPackingAfas;

class Test extends \Magento\Framework\App\Action\Action
{

	private $orderpush;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        BrouwersPackingAfas $orderpush
    ) {
        $this->orderpush = $orderpush;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	$this->orderpush->execute();

    }

}
