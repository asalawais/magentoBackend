<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Order;

/**
 * Blog home page view
 */

use Magento\Framework\App\ResourceConnection;

class SalesGrid extends \Magento\Framework\App\Action\Action
{

    private $resourceConnection;
    private $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*');
        echo count($collection);
        foreach ($collection as $order){
            $this->salesGridDataSave($order);
        }
    }

    public function salesGridDataSave($order){
        if (!$order->getId()) {
            return null;
        }

        $connection = $this->resourceConnection->getConnection();
        $data =
            [
                'afas_order_nbr'=>$order->getData('afas_order_nbr'),
                'afas_order_status' => $order->getData('afas_order_status'),
                'order_ref_nbr' => $order->getData('order_ref_nbr'),
                'debtor_id' => $order->getData('debtor_id'),
                'order_track_pod' => $order->getData('order_track_pod')

        ];
        //print_r($data);
        if ($data) {
            $connection->update(
                $this->resourceConnection->getTableName('sales_order_grid'),
                $data,
                [
                    'entity_id = ?' => $order->getId()
                ]
            );
        }
    }

}


