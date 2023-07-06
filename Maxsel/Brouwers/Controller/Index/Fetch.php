<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.
 */

namespace Maxsel\Brouwers\Controller\Index;

/**
 * Blog home page view
 */

use Magento\Framework\Webapi\Exception;
use Maxsel\Brouwers\Model\Order;

class Fetch extends \Magento\Framework\App\Action\Action
{

    private $brouwers;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Order                        $xml
    )
    {
        $this->brouwers = $xml;
        return parent::__construct($context);
    }


    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->brouwers->call();
        } catch (Exception $e) {

        }
    }

}
