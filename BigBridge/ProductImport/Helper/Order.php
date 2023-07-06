<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Helper;
use Magento\Framework\App\ResourceConnection;
use Magmodules\Channable\Model\Order\ResourceModel\CollectionFactory as ChannableOrders;

/**
 * Class ProductPublisher
 */
class Order
{
    protected $logger;
    protected $resourceConnection;
    private $orderChannable;


    public function __construct(

        \Psr\Log\LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        ChannableOrders $orderChannable
    ) {
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->orderChannable = $orderChannable;


    }
    public function salesGridDataSave($order){
        if (!$order->getId()) {
            return null;
        }

        $connection = $this->resourceConnection->getConnection();
        $data = [
            'afas_order_nbr'=>$order->getData('afas_order_nbr'),
            'afas_packing_slip_nbr' => $order->getData('afas_packing_slip_nbr'),
            'afas_order_status' => $order->getData('afas_order_status'),
            'order_ref_nbr' => $order->getData('order_ref_nbr'),
            'debtor_id' => $order->getData('debtor_id'),
            'order_track_pod' => $order->getData('order_track_pod')
        ];
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

    public function salesGridCollectionData(){
        $collection = $this->orderChannable->create();
      foreach ($collection as $order) {
          $connection = $this->resourceConnection->getConnection();
          $selectData = $connection->select()
              ->from(
                  $this->resourceConnection->getTableName('channable_orders'),
                  [
                      'channel_id',
                      'channable_id',
                      'channel_label',
                      'channel_name',
                  ]
              )->where('magento_order_id = ?', $order->getMagentoOrderId());
          $data = $connection->fetchRow($selectData);
          if ($data) {
              $connection->update(
                  $this->resourceConnection->getTableName('sales_order'),
                  $data,
                  [
                      'entity_id = ?' => $order->getMagentoOrderId()
                  ]
              );
          }
      }
    }

}
