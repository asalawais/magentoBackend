<?php
namespace BigBridge\ProductImport\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory as OrderFactory;
use Magento\Framework\App\ResourceConnection;
class OrderPlaceAfter implements ObserverInterface
{

    protected $logger;
    protected $orderFactory;
    protected $resourceConnection;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderFactory $orderFactory,
        ResourceConnection $resourceConnection

    )
    {
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;

    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->getChannableDataByOrder($observer->getOrder());

    }
    public function getChannableDataByOrder($order)
    {
        if (!$order->getEntityId()) {
            return null;
        }

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
            )->where('magento_order_id = ?', $order->getEntityId());
        $data = $connection->fetchRow($selectData);
        if ($data) {
            $connection->update(
                $this->resourceConnection->getTableName('sales_order'),
                $data,
                [
                    'entity_id = ?' => $order->getEntityId()
                ]
            );
        }
    }
}
