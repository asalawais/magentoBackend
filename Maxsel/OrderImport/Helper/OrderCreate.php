<?php
namespace Maxsel\OrderImport\Helper;

use Magento\Quote\Api\CartManagementInterface;

class OrderCreate extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_cartRepositoryInterface;
    protected $_cartManagementInterface;
    protected $_storeManager;
    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Catalog\Model\Product $product
     * @param Magento\Framework\Data\Form\FormKey $formKey $formkey,
     * @param Magento\Quote\Model\Quote $quote,
     * @param Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param Magento\Sales\Model\Service\OrderService $orderService,
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Model\Quote\Address\Rate $rate,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Sales\Model\Service\OrderService $orderService
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_productFactory = $productFactory;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->orderService = $orderService;
        $this->_cartRepositoryInterface = $cartRepository;
        $this->_cartManagementInterface = $cartManagement;
        $this->_shippingRate = $rate;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return array
     *
     */
    public function createMageOrder($orderData) {
        //print_r($orderData);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $area = $objectManager->get('Magento\Framework\App\State');
        if (!$area->getAreaCode()) {
            $area->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
        echo $currentAreaCode = $area->getAreaCode();
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $cartId = $this->_cartManagementInterface->createEmptyCart();
        $cart = $this->_cartRepositoryInterface->get($cartId)->setStore($store)->setCurrency()->setIsSuperMode(true);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_cartRepositoryInterface->get($cartId);


        $quote->setStore($store);
        // Set Customer Data on Qoute, Do not create customer.
        $quote->setCustomerFirstname($orderData['firstname']);
        $quote->setCustomerLastname($orderData['lastname']);
        $quote->setCustomerEmail($orderData['email']);
        $quote->setCustomerIsGuest(true);
        $quote->setCurrency();

        //add items in quote
        //print_r($orderData['items']);
        foreach($orderData['items'] as $item){
            $productModel = $this->_productFactory->create();
            $product=$productModel->load($item['product_id']);
            //echo $product->getSku();
            $product->setPrice($item['price']);
            //echo $item['qty'];
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        $this->_shippingRate
            ->setCode('freeshipping_freeshipping')
            ->getPrice(1);
        // Collect Rates and Set Shipping & Payment Method

        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('freeshipping_freeshipping'); //shipping method
        $quote->getShippingAddress()->addShippingRate($this->_shippingRate);
        $quote->setPaymentMethod('checkmo'); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'checkmo']);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);
        $order->setEmailSent(0);
        //$projID = $orderData['debtor_name'];
        $dateTimeZone = $this->timezone->date(new \DateTime($orderData['date']))->format('Y-m-d H:i:s');
        $order->setCreatedAt($dateTimeZone);
        $orderMo = $this->orderModel;
        $orderMo->loadByIncrementId($order->getRealOrderId());
        if($orderMo->getId()){
            $orderMo->setData('order_note', $orderData['note']);
            //$orderMo->setData('order_cod', $orderData['cod']);
            //$orderMo->setData('debtor_id', $orderData['debtor']);
            //$orderMo->setData('order_project', $projID);
            $orderMo->setData('order_ref_nbr', $orderData['ref']);
            $orderMo->setData('created_at', $dateTimeZone);
            $orderMo->setData('updated_at', $dateTimeZone);
            $orderMo->save();
        }
        $increment_id = $order->getRealOrderId();
        if($order->getEntityId()){
            $result['order_id']= $order->getRealOrderId();
            $result['order_ref'] = $orderData['ref'];
            $result['order_number'] = $order->getRealOrderId();
        }else{
            $result=['error'=>1,'msg'=>'Error found in this Afas order '.$orderData['ref']];
        }
        return $result;
    }
}
