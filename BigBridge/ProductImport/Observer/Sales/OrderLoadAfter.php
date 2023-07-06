<?php
namespace BigBridge\ProductImport\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
class OrderLoadAfter implements ObserverInterface
{

    const AFAS_ORDER_NBR = 'afas_order_nbr';
    const ORDER_REF_NBR = 'order_ref_nbr';
    const ORDER_PROJECT = 'order_project';
    const DEBTOR_ID = 'debtor_id';
    const ORDER_COD = 'order_cod';
    const ORDER_DUMMY = 'order_dummy';

public function execute(\Magento\Framework\Event\Observer $observer)
{
    $order = $observer->getOrder();

    $extensionAttributes = $order->getExtensionAttributes();

    if ($extensionAttributes === null) {
        $extensionAttributes = $this->getOrderExtensionDependency();

    }

        $afasOrderNbr = $order->getData(self::AFAS_ORDER_NBR);
        $orderRefNbr = $order->getData(self::ORDER_REF_NBR);
        $orderProject = $order->getData(self::ORDER_PROJECT);
        $debtorId = $order->getData(self::DEBTOR_ID);
        $orderCod = $order->getData(self::ORDER_COD);
        $orderDummy = $order->getData(self::ORDER_DUMMY);
        //$orderCod = $order->getData('customer_email');
        //print_r($extensionAttributes);
        //die;
        //die('hfghhg');
        $extensionAttributes->setAfasOrderNbr($afasOrderNbr);
        $extensionAttributes->setOrderRefNbr($orderRefNbr);
        $extensionAttributes->setOrderProject($orderProject);
        $extensionAttributes->setDebtorId($debtorId);
        $extensionAttributes->setOrderCod($orderCod);
        $extensionAttributes->setOrderDummy($orderDummy);
        //print_r($extensionAttributes);


    $order->setExtensionAttributes($extensionAttributes);
}
private function getOrderExtensionDependency()
{
    $orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get(
        '\Magento\Sales\Api\Data\OrderExtension'
    );
    return $orderExtension;
}
}
