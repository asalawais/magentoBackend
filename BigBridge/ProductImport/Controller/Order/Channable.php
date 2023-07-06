<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.
 */

namespace BigBridge\ProductImport\Controller\Order;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use \Magmodules\Channable\Service\Order\Import as OrderModel;

/**
 * Blog home page view
 */
class Channable extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    private $cartRepositoryInterface;
    private $cartManagementInterface;
    private $orderModel;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context               $context,
        \Magento\Framework\View\Result\PageFactory          $resultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface         $orderRepository,
        \Magento\Store\Model\StoreManagerInterface          $storeManager,
        \Magento\Customer\Model\CustomerFactory             $customerFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface   $customerRepository,
        \Magento\Quote\Model\QuoteFactory                   $quote,
        \Magento\Quote\Model\QuoteManagement                $quoteManagement,
        OrderModel                                          $orderModel,
        CartRepositoryInterface                             $cartRepositoryInterface,
        CartManagementInterface                             $cartManagementInterface,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->orderModel = $orderModel;
        parent::__construct($context);
    }

    /**
     * Hello World Action Page
     *
     * @return void
     */
    public function execute()
    {
        $orderData = [
            'price' => [
                'currency' => 'EUR',
                'shipping' => 0
            ],
            'customer' => [
                'company' => '',
                'first_name' => 'Piet Pool',
                'middle_name' => '',
                'email' => 'bcd1j3qq43q3jn8@marketplace.amazon.nl', //customer email id
                'last_name' => 'Piet Pool',
                'address_line_1' => 'saffier',
                'address_line_2' => '',
                'street' => 'saffier',
                'house_number' => '78',
                'house_number_ext' => '',
                'city' => 'Anna Paulowna',
                'country_code' => 'NL',
                'state_code' => 'Noord-Holland',
                'zip_code' => '1761VH',
                'vat_id' => '',
                'telephone' => '31653301437',
            ],
            'billing' => [
                'company' => '',
                'first_name' => 'Piet Pool',
                'middle_name' => '',
                'last_name' => 'Piet Pool',
                'email' => 'bcd1j3qq43q3jn8@marketplace.amazon.nl', //customer email id
                'address_line_1' => 'saffier',
                'address_line_2' => '',
                'street' => 'saffier',
                'house_number' => 78,
                'house_number_ext' => '',
                'city' => 'Anna Paulowna',
                'country_code' => 'NL',
                'state_code' => 'Noord-Holland',
                'zip_code' => '1761VH',
                'vat_id' => '',
                'telephone' => '31653301437',
            ],
            'shipping' => [
                'company' => '',
                'first_name' => 'Piet Pool',
                'middle_name' => '',
                'last_name' => 'Piet Pool',
                'email' => 'bcd1j3qq43q3jn8@marketplace.amazon.nl', //customer email id
                'address_line_1' => 'saffier',
                'address_line_2' => '',
                'street' => 'saffier',
                'house_number' => 78,
                'house_number_ext' => '',
                'city' => 'Anna Paulowna',
                'country_code' => 'NL',
                'state_code' => 'Noord-Holland',
                'zip_code' => '1761VH',
                'vat_id' => '',
                'telephone' => '31653301437',
            ],
            'products' => [
                [
                    'id' => 167,
                    'quantity' => 1,
                    'title' => 'Hotel Home Collection - Topper Hoeslaken - 160x200/210/220+20 cm - Creme',
                    'ean' => '31653301437',
                    'price' => 13.95,
                    'delivery_period' => ''
                ],
                [
                    'id' => 172,
                    'quantity' => 1,
                    'title' => 'Hotel Home Collection - Topper Hoeslaken - 160x200/210/220+20 cm - Zilver Grijs',
                    'ean' => '31653301437',
                    'price' => 13.95,
                    'delivery_period' => ''
                ]
            ],
            'channel_id' => '1212122121',
            'channable_id' => '12344-45454',
            'order_status' => ''
        ];

        /*$orderInfo = [
            'email' => 'bcd1j3qq43q3jn8@marketplace.amazon.nl', //customer email id
            'currency_id' => 'EUR',
            'address' => [
                'firstname' => 'Piet Pool',
                'lastname' => 'P.Pool',
                'prefix' => '',
                'suffix' => '',
                'street' => 'saffier, 78',
                'city' => 'Anna Paulowna',
                'country_id' => 'NL',
                'region' => 'Noord-Holland',
                'region_id' => null, // State region id
                'postcode' => '1761VH',
                'telephone' => '31653301437',
                'fax' => '',
                'save_in_address_book' => 0
            ],
            'items' =>
                [
                    //simple product
                    [
                        'product_id' => '167',
                        'qty' => 1
                    ],
                    [
                        'product_id' => '172',
                        'qty' => 1
                    ],
                    //configurable product
                    [
                        'product_id' => '70',
                        'qty' => 2,
                        'super_attribute' => [
                            93 => 52,
                            142 => 167
                        ]
                    ]
                ]
        ];*/
        print_r($orderData);
        $storeId =1;
        $response = $this->orderModel->importOrder($orderData, $storeId);
        print_r($response);
    }
}
