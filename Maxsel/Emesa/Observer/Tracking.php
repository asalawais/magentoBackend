<?php
namespace Maxsel\Emesa\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Maxsel\Emesa\Helper\Connect;

class Tracking implements ObserverInterface {

    protected $logger;
    /**
     * @var Connect
     */
    protected $connect;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Connect $connect
        ) {
        $this->logger = $logger;
        $this->connect = $connect;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $observer->getEvent()->getTrack();

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $track->getShipment();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $marketOrderId = $order->getData('order_ref_nbr');
        $channelName = $order->getData('channel_name');
        $this->logger->info('Before Tracking: Channel Name '.$channelName. ' MarketOrderId '.$marketOrderId);
        if($channelName == 'emesa' && $marketOrderId){
            $this->logger->info('Tracking: Channel Name '.$channelName. ' MarketOrderId '.$marketOrderId);
            $this->connect->putShipments($shipment, $track, $marketOrderId);
        }
        else{
            return false;
        }
    }
}
