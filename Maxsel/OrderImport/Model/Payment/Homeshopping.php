<?php
/**
 * Copyright © 2021 Maxsel.nl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Maxsel\OrderImport\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Homeshopping
 *
 * @package Maxsel\OrderImport\Model\Payment
 */
class Homeshopping extends AbstractMethod
{

    const CODE = 'homeshopping';
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var bool
     */
    protected $_isOffline = true;
    /**
     * @var bool
     */
    protected $_canUseCheckout = false;
    /**
     * @var bool
     */
    protected $_canUseInternal = true;
    /**
     * @var string
     */
    protected $_infoBlockType = 'Maxsel\OrderImport\Block\Info\Homeshopping';

    /**
     * @param CartInterface|null $quote
     *
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }
}
