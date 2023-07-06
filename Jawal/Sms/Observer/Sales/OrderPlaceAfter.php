<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Jawal\Sms\Observer\Sales;
use Jawal\Sms\Helper\Data;
//  it's for new order placed.
class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected $_Helper;
    protected $_menuConfig;

    public function __construct(
        Data $data
    ) {
        $this->_Helper = $data;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
      /** @var \Magento\Sales\Model\Order $order */
      $order = $observer->getOrder();
      $dataArr = array(
        'order_status' => $order->getData('status'),
        'order_id' => $order->getId(),
		'order_number' => $order->getIncrementId(),
        'store_id' => $order->getStoreId(),
        'customer_id' => $order->getCustomerId(),
        'customer_firstname' => $order->getCustomerFirstname(),
        'customer_lastname' => $order->getCustomerLastname(),
        'customer_email' => $order->getCustomerEmail(),
        'customer_telephone' => $order->getCustomerTelephone(),
        'shipping_telephone' => $order->getShippingAddress()->getTelephone(),
        'billing_telephone' => $order->getBillingAddress()->getTelephone(),
      );
      $this->_Helper->sendSms('order_new', $dataArr);
    }
}
