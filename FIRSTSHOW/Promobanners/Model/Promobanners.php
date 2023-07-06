<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace FIRSTSHOW\Promobanners\Model;

class Promobanners extends \Magento\Framework\Model\AbstractModel
{
   
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('FIRSTSHOW\Promobanners\Model\ResourceModel\Promobanners');
    }
}
