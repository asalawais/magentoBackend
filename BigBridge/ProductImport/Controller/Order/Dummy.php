<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Order;

/**
 * Blog home page view
 */

use BigBridge\ProductImport\Cron\OrderpushEmesa;
use BigBridge\ProductImport\Cron\OrderpushHomeShoppingDummy;

class Dummy extends \Magento\Framework\App\Action\Action
{

    private $orderpush;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        OrderpushHomeShoppingDummy $orderpush

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
        //$this->_view->loadLayout();
        //$this->_view->renderLayout();
    }

}
