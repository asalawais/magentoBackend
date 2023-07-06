<?php
namespace BigBridge\ProductImport\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
//use Aheadworks\CreditLimit\Model\Customer\CreditLimit\DataProvider as CreditLimitDataProvider;
//use Aheadworks\CreditLimit\Model\Customer\CreditLimit\Provider\CreditLimit as CreditLimit;
//use Aheadworks\CreditLimit\Model\Customer\Backend\BalanceUpdater;
use Magento\Sales\Model\OrderFactory as OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ResourceConnection;
class OrderSaveAfter implements ObserverInterface
{

    const AFAS_ORDER_NBR = 'afas_order_nbr';
    const ORDER_REF_NBR = 'order_ref_nbr';
    const ORDER_PROJECT = 'order_project';
    const DEBTOR_ID = 'debtor_id';
    const ORDER_COD = 'order_cod';

    protected $logger;
    protected $_customerSession;
    protected $customerRepository;
    protected $_creditLimitDataProvider;
    protected $_creditLimit;
    protected $orderFactory;
    protected $_checkoutSession;
    protected $resourceConnection;
    /**
     * @var BalanceUpdater
     */
    private $balanceUpdater;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $session,
        CustomerRepositoryInterface $customerRepository,
        // CreditLimitDataProvider $creditLimitDataProvider,
        // CreditLimit $creditLimit,
        OrderFactory $orderFactory,
        //BalanceUpdater $balanceUpdater,
        ResourceConnection $resourceConnection,
        CheckoutSession $checkoutSession

    )
    {

        $this->logger = $logger;
        $this->_customerSession = $session;
        $this->customerRepository = $customerRepository;
        // $this->_creditLimitDataProvider = $creditLimitDataProvider;
        // $this->_creditLimit = $creditLimit;
        $this->orderFactory = $orderFactory;
        // $this->balanceUpdater = $balanceUpdater;
        $this->resourceConnection = $resourceConnection;
        $this->_checkoutSession = $checkoutSession;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$this->logger->debug('Ordersaveafter:'.print_($this->_checkoutSession->getData(), true));
        if ($id = $this->_customerSession->getCustomer()->getId()) {
            $debtorName = $this->_customerSession->getCustomer()->getName();
            $order = $observer->getOrder();

            //$this->logger->debug(print_r($comment, true));

            $order->setDebtorId($this->getDebtorId($id)->getValue());
            //$order->setOrderNote("Test Order");
            $order->setOrderProject($debtorName);
            //$order->setOrderRefNbr($order->getId());
            $order->setOrderCod(0);
            $order->save();
        }
        $this->getChannableDataByOrder($observer->getOrder());

    }

    public function getDebtorId($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $customer->getCustomAttribute('debtor_id');
    }


    public function getChannableDataByOrder($order)
    {
        if (!$order->getEntityId()) {
            return null;
        }
        $order->setData('weight', 1);
        $order->setData('shipping_method','dpd_dpd');
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
