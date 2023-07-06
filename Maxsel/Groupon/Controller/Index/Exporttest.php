<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace Maxsel\Groupon\Controller\Index;

use Maxsel\Groupon\Helper\Connect;

/**
 * Blog home page view
 */


class Exporttest extends \Magento\Framework\App\Action\Action
{

	private $productsArray;
    protected $connect;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        Connect $connect
    ) {
        $this->connect = $connect;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->connect->putProducts();
        echo "done";
        //$this->_view->loadLayout();
        //$this->_view->renderLayout();
    }

}
