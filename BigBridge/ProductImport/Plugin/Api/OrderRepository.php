<?php

namespace BigBridge\ProductImport\Plugin\Api;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class OrderRepository {

	const AFAS_ORDER_NBR = 'afas_order_nbr';
	const ORDER_REF_NBR = 'order_ref_nbr';
	const ORDER_PROJECT = 'order_project';
	const DEBTOR_ID = 'debtor_id';
	const ORDER_COD = 'order_cod';
    const ORDER_DUMMY = 'order_dummy';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    protected $_orderCollectionFactory;


    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    {
        $this->extensionFactory = $extensionFactory;
         $this->_orderCollectionFactory = $orderCollectionFactory;
    }

/*public function afterGet(\Magento\Sales\Api\OrderRepositoryInterface $subject, $entity) {

    $extensionAttributes = $entity->getExtensionAttributes ();


    if ($extensionAttributes) {
        $extensionAttributes->setAfasOrderNbr('afas_order_nbr');
        $extensionAttributes->setOrderRefNbr('order_ref_nbr');
        $extensionAttributes->setOrderProject('order_project');
        $extensionAttributes->setDebtorId('debtor_id');
        $extensionAttributes->setOrderCod('order_cod');
        $entity->setExtensionAttributes($extensionAttributes);
    }
    return $entity;
}*/

/**
     * Add "delivery_type" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $afasOrderNbr = $order->getData(self::AFAS_ORDER_NBR);
        $orderRefNbr = $order->getData(self::ORDER_REF_NBR);
        $orderProject = $order->getData(self::ORDER_PROJECT);
        $debtorId = $order->getData(self::DEBTOR_ID);
        $orderCod = $order->getData(self::ORDER_COD);
        $orderDummy = $order->getData(self::ORDER_DUMMY);
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        //$extensionAttributes->setDeliveryType($deliveryType);
        $extensionAttributes->setAfasOrderNbr($afasOrderNbr);
        $extensionAttributes->setOrderRefNbr($orderRefNbr);
        $extensionAttributes->setOrderProject($orderProject);
        $extensionAttributes->setDebtorId($debtorId);
        $extensionAttributes->setOrderCod($orderCod);
        $extensionAttributes->setOrderDummy($orderDummy);
        //$entity->setExtensionAttributes($extensionAttributes);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Add "delivery_type" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $afasOrderNbr = $order->getData(self::AFAS_ORDER_NBR);
	        $orderRefNbr = $order->getData(self::ORDER_REF_NBR);
	        $orderProject = $order->getData(self::ORDER_PROJECT);
	        $debtorId = $order->getData(self::DEBTOR_ID);
	        $orderCod = $order->getData(self::ORDER_COD);
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setAfasOrderNbr($afasOrderNbr);
	        $extensionAttributes->setOrderRefNbr($orderRefNbr);
	        $extensionAttributes->setOrderProject($orderProject);
	        $extensionAttributes->setDebtorId($debtorId);
	        $extensionAttributes->setOrderCod($orderCod);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }

   /* public function getOrderCollection()
   {
       $collection = $this->_orderCollectionFactory->create()
         ->addAttributeToSelect('*')
         ->addFieldToFilter($field, $condition); //Add condition if you wish

     return $collection;

    }*/


}
