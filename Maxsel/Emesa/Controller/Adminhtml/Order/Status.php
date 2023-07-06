<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\Emesa\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Maxsel\Emesa\Helper\Connect;

class Status implements HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Http
     */
    protected $http;

    protected $_orderCollectionFactory;

    /**
     * @var Connect
     */
    protected $connect;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     */
    public function __construct(
        PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Json $json,
        LoggerInterface $logger,
        Connect $connect,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->connect = $connect;
        $this->http = $http;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            echo count($this->getOrders());
            echo "<br>";
            foreach ($this->getOrders() as $order){
                $tracksCollection = $order->getTracksCollection();
                $marketOrderId = $order->getData('order_ref_nbr');
                $channelName = $order->getData('channel_name');
                echo $order->getIncrementId();
                $this->logger->info('Before Tracking: Channel Name '.$channelName. ' MarketOrderId '.$marketOrderId);
                foreach ($tracksCollection->getItems() as $track) {
                    $shipment = $track->getShipment();
                    $this->connect->putShipments($shipment, $track, $marketOrderId);

                }


            }
            return $this->jsonResponse('your response');
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return ResultInterface
     */
    public function jsonResponse($response = '')
    {
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json');
        return $this->http->setBody(
            $this->serializer->serialize($response)
        );
    }

    public function getOrders()
    {
        $from = '5000000052';
        $to = '5000000077';
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 'complete')
            //->addFieldToFilter('entity_id', array("eq" => 12)) //for testing
            ->addFieldToFilter('state', 'complete')
            //->addFieldToFilter('channable_id', array("neq" => 'NULL'))
            //->addFieldToFilter('channel_id', array("neq" => 'NULL'))
            ->addFieldToFilter('channel_name', 'emesa')
            ->addFieldToFilter('order_ref_nbr', array("neq" => 'NULL'))
            ->addFieldToFilter('store_id', 5)
            ->addFieldToFilter('increment_id',
                ['gteq' => $from]
            )
            ->addFieldToFilter('increment_id',
                ['lteq' => $to]
            )
            //->addFieldToFilter('afas_order_nbr', array("null" => true))
            ->setOrder(
                'created_at',
                'asc'
            );
        //echo count($collection);
        return $collection;
    }
}

