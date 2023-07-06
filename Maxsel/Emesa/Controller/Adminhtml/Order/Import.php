<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\Emesa\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magmodules\Channable\Api\Order\Data\DataInterface as ChannableOrderData;
use Psr\Log\LoggerInterface;
use Maxsel\Emesa\Helper\Connect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Maxsel\Emesa\Helper\OrderCreate;
use Maxsel\Emesa\Model\Order as ExtendedOrder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;

class Import implements HttpPostActionInterface
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
    /**
     * @var Connect
     */
    protected $connect;

    /**
     * @var OrderCreate
     */
    protected $orderCreate;

    protected $newOrder;

    protected $customerRepository;

    protected $productRepository;

    protected $orderCollectionFactory;

    protected $order;

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
        Json $json,
        LoggerInterface $logger,
        Connect $connect,
        OrderCreate $orderCreate,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $orderCollectionFactory,
        ProductRepositoryInterface  $productRepository,
        TimezoneInterface $timezone,
        Order $order,
        ExtendedOrder $newOrder,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->connect = $connect;
        $this->orderCreate = $orderCreate;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->order = $order;
        $this->newOrder = $newOrder;
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
            /**
             * @var \Emesa\PartnerPlatform\Model\OrderList $orderList
             */
            $orderList = $this->connect->listOrders();
            $this->logger->info(print_r($orderList, true));
            //print_r($orderList);
            $result = [];
            if($orderList->getTotal() > 0) {
                $result = $this->orderProcess($orderList);
            }
            $result['orders'] = $orderList->getTotal();
            return $this->jsonResponse($result);
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

    public function orderProcess(\Emesa\PartnerPlatform\Model\OrderList $ordersList){
        $this->logger->info('Emesa OrderNew: start');
        $orderReferences = [];
        $resultArr = [];


        foreach ($ordersList->getData() as $orderDataN) {
            try {
                $productsList = array();
                $codAdded = 0;
                foreach ($orderDataN['lines'] as $orderLines) {
                    //$this->logger->debug('$orderLines: '. print_r($orderLines, true));
                    //$orderLines->supplierProductId = '8717752016440';
                    /** @var \Emesa\PartnerPlatform\Model\OrderLineDto $orderLines */
                    $supplierProductId = $orderLines->getSupplierProductId();
                    //$position = strpos($orderLines->getSupplierProductId(), 'ui-');
                    //$this->logger->info('position'. $position);
                    //if ($position !== false) {
                    $supplierProductId = str_replace("ui-","",$orderLines->getSupplierProductId());
                        //$this->logger->info($supplierProductId);
                    //}

                    $productsList[] = [
                        'id' => $this->getProductBySku($supplierProductId)->getEntityId(),
                        'quantity' => $orderLines->getQuantity(),
                        'price' => (int)$orderLines->getUnitPriceInclVatInCents()/100,
                        'title'           => 'Title',
                        'ean'             => $orderLines->getSupplierProductId(),
                        'delivery_period' => ''
                    ];
                    //$this->logger->debug('$productsList: '. print_r($productsList, true));
                }
                //$orderDataN['totalShippingCostsInclVatInCents'];

                //$orderDataN['address']->digiCode;
                /** @var \Emesa\PartnerPlatform\Model\OrderDto $orderDataN */
                //$orderDataN->setCustomerEmail('almrsystems@gmail.com');

                $ordRef = $this->getOrderRef($orderDataN->getMarketOrderId());
                if ($orderDataN->getCustomerEmail()!='' && count($ordRef) === 0) {
                    /**
                     * @var ChannableOrderData $tempOrder;
                     */

                    $address = $orderDataN->getAddress();
                    /** @var \Emesa\PartnerPlatform\Model\AddressDto $address */
                    $tempOrder = [
                        'currency_id' => 'EUR',
                        /** @var type $orderData */
                        'email' => $orderDataN->getCustomerEmail(), //buyer email id
                        'date' => $orderDataN->getOrderDateTime(),
                        'ref' => $orderDataN->getMarketOrderId(),
                        'note' => $orderDataN->getDeliveryInstructions(),
                        'order_status' => 'new',
                        'emesa_id' => $orderDataN->getMarketOrderId(),
                        'channel_name' => 'emesa',
                        'channel_id' => $orderDataN->getMarketOrderId(),
                        'channable_channel_label' => 'Emesa.nl',
                        'customer' => [
                            'first_name' => $address->getName(),
                            'middle_name' => '',
                            'last_name' => $address->getName(),
                            'email' => $orderDataN->getCustomerEmail(),
                            'mobile' => $orderDataN->getCustomerPhoneNumber(),
                            'phone' => $orderDataN->getCustomerPhoneNumber(),

                        ],
                        'shipping' => [
                            'first_name' =>  $address->getName(), //address Details
                            'middle_name' => '',
                            'last_name' =>  $address->getName(),
                            'company' => $address->getCompany(),
                            'street' => $address->getStreet(),
                            'house_number' => $address->getHouseNumber(),
                            'house_number_ext' => $address->getHouseNumberAddition(). ' '.$address->getFloorNumber(),
                            'city' => $address->getCity(),
                            'country_code' => $address->getCountryIso3166(),
                            'state_code' => '',
                            'zip_code' => $address->getZipcode(),
                            'telephone' => $orderDataN->getCustomerPhoneNumber(),
                            'email'       => $orderDataN->getCustomerEmail(),
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'billing' => [
                            'first_name' =>  $address->getName(), //address Details
                            'middle_name' => '',
                            'last_name' =>  $address->getName(),
                            'company' => $address->getCompany(),
                            'street' => $address->getStreet(),
                            'house_number' => $address->getHouseNumber(),
                            'house_number_ext' => $address->getHouseNumberAddition(). ' '.$address->getFloorNumber(),
                            'city' => $address->getCity(),
                            'country_code' => $address->getCountryIso3166(),
                            'state_code' => '',
                            'zip_code' => $address->getZipcode(),
                            'telephone' => $orderDataN->getCustomerPhoneNumber(),
                            'email'       => $orderDataN->getCustomerEmail(),
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'price' => [
                            'shipping' => (int)$orderDataN->getTotalShippingCostsInclVatInCents()/100,
                            'currency' => 'EUR'
                        ],
                        'products' => $productsList
                    ];

                    //$result = $this->orderCreate->createMageOrder($tempOrder);
                    $this->logger->info('$tempOrder: '.print_r($tempOrder, true));

                    $result = $this->newOrder->importOrder($tempOrder, 5);
                    $resultArr[] = $result;
                    $resultArr['order_success'][] = $result;
                    $orderResponse[] = $result;

                } else {
                    //same order reference found
                    if($orderDataN->getCustomerEmail()==''){
                        $this->logger->info('marketOrderId: '. $orderDataN->getMarketOrderId(). ' email not found');
                    }
                    else{
                        $this->logger->info('marketOrderId: '. $orderDataN->getMarketOrderId(). ' already exist');
                    }
                    $resultArr['reference_error'][] = $orderDataN->getMarketOrderId();
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                //$resultArr[] = $e->getMessage();
                //$resultArr['product_error'][] = ['ref_number' => $orderData['Klant (order)'], 'sku'=>$orderData['EAN ( EAN nummer MPS)'], 'error'=>$e->getMessage()];
                //$resultArr['product_error'][$orderData['EAN ( EAN nummer MPS)']] = $e->getMessage();
                $this->logger->info($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
                //$resultArr[] = $e->getMessage();
                //$resultArr['product_error'][] = ['ref_number' => $orderData['Klant (order)'], 'sku'=>$orderData['EAN ( EAN nummer MPS)'], 'error'=>$e->getMessage()];
                //$resultArr['product_error'][$orderData['Klant (order)']][$orderData['EAN ( EAN nummer MPS)']] = $e->getMessage();

            }
        }

        return $resultArr;
    }
    public function getOrderRef($ref)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_ref_nbr', $ref);
            //->addFieldToFilter('debtor_id', $debtorId);
        return $collection;
    }

    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    private function getOrder($id)
    {
        return $this->order->loadByIncrementId($id);
    }
}

