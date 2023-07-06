<?php


namespace Maxsel\Brouwers\Model;

use Magento\Framework\App\Action\Context;

class Shipment
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    protected $orderModel;

    /**
     * @param Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order $convertOrder
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Psr\Log\LoggerInterface     $logger,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
        $this->orderModel = $orderModel;
        $this->logger = $logger;
        $this->_shipmentNotifier = $shipmentNotifier;
    }

    /**
     * Test Order Create Shipment Controller
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipment($orderIncrementId, $tackingInfo)
    {
        //$order = $this->_orderRepository->get($orderIncrementId);
        $order = $this->orderModel->loadByIncrementId($orderIncrementId);

        // to check order can ship or not
        if ($order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("You can't create the Shipment of this order.")
            );
        }
        /**
         * @var \Magento\Sales\Model\Order $order
         */

        $orderShipment = $this->_convertOrder->toShipment($order);
        $orderShipment->getExtensionAttributes()->setSourceCode('default');
        foreach ($order->getAllItems() as $orderItem) {
            // Check virtual item and item Quantity
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
            echo $shipmentItem->getId();
            $orderShipment->addItem($shipmentItem);
        }

        $orderShipment->register();
        $orderShipment->getOrder()->setIsInProcess(true);
        try {
            // Save created Order Shipment
            //$track = $this->trackFactory->create()->addData($tackingInfo);
            //$orderShipment->addTrack($track)->save();
            $orderShipment->save();
            $orderShipment->getOrder()->save();

            // Send Shipment Email
            //$this->_shipmentNotifier->notify($orderShipment);
            $orderShipment->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    public function addTrackingToExistingShipment($orderShipment, $tackingInfo, $order)
    {
        //$orderShipment->getExtensionAttributes()->setSourceCode('default');
        //$orderShipment->register();
        //$orderShipment->getOrder()->setIsInProcess(true);
        $trackNumbers = [];
        $tracksCollection = $order->getTracksCollection();
        foreach ($tracksCollection->getItems() as $track) {
            $trackNumbers[] = $track->getTrackNumber();
        }
        if(!in_array($tackingInfo['number'], $trackNumbers)) {
            try {
                // Save created Order Shipment
                $track = $this->trackFactory->create()->addData($tackingInfo);
                $orderShipment->addTrack($track)->save();
                $orderShipment->save();
                $orderShipment->getOrder()->save();

                // Send Shipment Email
                //$this->_shipmentNotifier->notify($orderShipment);
                $orderShipment->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }
    }
}
