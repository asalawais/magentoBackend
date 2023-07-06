<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Jawal\Sms\Observer\Sales;
use Jawal\Sms\Helper\Data;

class OrderShipmentTrackSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $_Helper;
    protected $_menuConfig;
    protected $logger;

    public function __construct(
        Data $data,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_Helper = $data;
        $this->logger = $logger;
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
      /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
      $track = $observer->getTrack();
      // $trackData = $track->getData();
      // $shipmentData = $shipment->getData();

      // $this->logger->info('shipmentData', $shipmentData);

      if ($track->hasDataChanges() &&
          ($track->getCreatedAt() == $track->getUpdatedAt() || $track->dataHasChangedFor('track_number'))) {

          // if ($this->_registry->registry('magesms_track_obj')) {
          //     $this->_registry->unregister('magesms_track_obj');
          // }
          // $this->_registry->register('magesms_track_obj', $track);

          // $this->_magesms->runHook(
          //     'update_order_tracking_number',
          //     new Extensions\Hook\Variables([
          //         'order_id' => $track->getId(),
          //         'store_id' => $track->getStoreId(),
          //         'customer_id' => $track->getShipment()->getCustomerId(),
          //         'customer_firstname' => $track->getShipment()->getCustomerFirstname(),
          //         'customer_lastname' => $track->getShipment()->getCustomerLastname(),
          //         'customer_email' => $track->getShipment()->getCustomerEmail(),
          //     ]),
          //     $observer
          // );
          $orderid = $track->getId();
          $getStoreId = $track->getStoreId();

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderid);

          $shipment = $track->getShipment(true);
          $tracksCollection = $shipment->getTracksCollection();
          $trackNumbers = [];
          foreach ($tracksCollection->getItems() as $track) {
              $trackNumbers[] = $track->getTrackNumber();
              $trackTitle[] = $track->getTitle();
          }
          $trackNumbersStr = ($trackNumbers) ? implode(",", $trackNumbers) : "";
          $trackTitleStr = ($trackTitle) ? implode(",", $trackTitle) : "";

          $dataArr = array(
            // 'order_status' => $order->getStatus(),
            'order_id' => $orderid,
			'order_number' => $order->getIncrementId(),
            'store_id' => $getStoreId,
            'customer_id' => $shipment->getCustomerId(),
            'customer_firstname' => $shipment->getCustomerFirstname(),
            'customer_lastname' => $shipment->getCustomerLastname(),
            'customer_email' => $shipment->getCustomerEmail(),

            'customer_telephone' => $order->getCustomerTelephone(),
            'shipping_telephone' => $order->getShippingAddress()->getTelephone(),
            'billing_telephone' => $order->getBillingAddress()->getTelephone(),

            'track_number' => $trackNumbersStr,
            'carrier_title' => $trackTitleStr,

          );
          // $this->logger->info('$trackNumbers', $trackNumbers);
          // $this->logger->info('$trackTitle', $trackTitle);
          // $this->logger->info('$dataArr ', $dataArr);

          $this->_Helper->sendSms('order_tracking', $dataArr);
      }

    }
}
