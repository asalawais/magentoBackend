<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\ImportExportWP\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Maxsel\ImportExportWP\Helper\Connect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Maxsel\ImportExportWP\Helper\OrderCreate;
use Maxsel\ImportExportWP\Helper\Order as ExtendedOrder;
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
            $orderList = $this->connect->getOrders();
            //$this->logger->debug($orderList->getTotal());
            if(count($orderList) > 0) {
               $result = $this->orderProcess($orderList);
            }
            //return $this->jsonResponse($result);
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

    public function orderProcess($ordersList){
        $this->logger->debug('WP OrderNew: start');
        $orderReferences = [];
        foreach ($ordersList as $orderDataN) {
            try {
                $productsList = array();
                $codAdded = 0;
                foreach ($orderDataN->line_items as $orderLines) {
                    $productsList[] = [
                        'id' => $this->getProductBySku($orderLines->sku)->getEntityId(),
                        'quantity' => $orderLines->quantity,
                        'price' => $orderLines->price,
                        'title'           => $orderLines->name,
                        'ean'             => $orderLines->sku,
                        'delivery_period' => ''
                    ];
                    //$this->logger->debug('$productsList: '. print_r($productsList, true));
                }
                //$orderDataN['totalShippingCostsInclVatInCents'];
                //$orderDataN->setCustomerEmail('almrsystems@gmail.com');

                $ordRef = $this->getOrderRef($orderDataN->id);
                $address = $orderDataN->billing;
                if ($address->email!='' && count($ordRef) === 0) {
                    $tempOrder = [
                        'currency_id' => 'EUR',

                        'email' => $address->email, //buyer email id
                        'date' => $orderDataN->date_created,
                        'ref' => $orderDataN->id,
                        'note' => 'Testing',
                        'order_status' => 'new',
                        'wp_id' => $orderDataN->id,
                        'channel_name' => 'Boxspringgekte.nl',
                        'channel_id' => $orderDataN->id,
                        'channable_channel_label' => 'boxspringgekte.nl',
                        'customer' => [
                            'first_name' => $address->first_name,
                            'middle_name' => '',
                            'last_name' => $address->last_name,
                            'email' => $address->email,
                            'mobile' => $address->phone,
                            'phone' => $address->phone,

                        ],
                        'shipping' => [
                            'first_name' =>  $address->first_name, //address Details
                            'middle_name' => '',
                            'last_name' =>  $address->last_name,
                            'company' => $address->company,
                            'street' => $address->address_1,
                            'house_number' => $address->address_2,
                            'house_number_ext' => '',
                            'city' => $address->city,
                            'country_code' => $address->country,
                            'state_code' => $address->state,
                            'zip_code' => $address->postcode,
                            'telephone' => $address->phone,
                            'email'       => $address->email,
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'billing' => [
                            'first_name' =>  $address->first_name, //address Details
                            'middle_name' => '',
                            'last_name' =>  $address->last_name,
                            'company' => $address->company,
                            'street' => $address->address_1,
                            'house_number' => $address->address_2,
                            'house_number_ext' => '',
                            'city' => $address->city,
                            'country_code' => $address->country,
                            'state_code' => $address->state,
                            'zip_code' => $address->postcode,
                            'telephone' => $address->phone,
                            'email'       => $address->email,
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'price' => [
                            'shipping' => $orderDataN->shipping_total,
                            'currency' => 'EUR'
                        ],
                        'products' => $productsList
                    ];

                    //$result = $this->orderCreate->createMageOrder($tempOrder);
                    $this->logger->debug('$tempOrder: '.print_r($tempOrder, true));
                    $result = $this->newOrder->importOrder($tempOrder, 4);
                    $resultArr[] = $result;
                    $resultArr['order_success'][] = $result;
                    $orderResponse[] = $result;

                } else {
                    //same order reference found
                    if($address->email==''){
                        $this->logger->debug('wpOrderID: '. $orderDataN->id. ' email not found');
                    }
                    else{
                        $this->logger->debug('wpOrderID: '. $orderDataN->id. ' already exist');
                    }
                    $resultArr['reference_error'][] = $orderDataN->id;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                //$resultArr[] = $e->getMessage();
                //$resultArr['product_error'][] = ['ref_number' => $orderData['Klant (order)'], 'sku'=>$orderData['EAN ( EAN nummer MPS)'], 'error'=>$e->getMessage()];
                //$resultArr['product_error'][$orderData['EAN ( EAN nummer MPS)']] = $e->getMessage();
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                //$resultArr[] = $e->getMessage();
                //$resultArr['product_error'][] = ['ref_number' => $orderData['Klant (order)'], 'sku'=>$orderData['EAN ( EAN nummer MPS)'], 'error'=>$e->getMessage()];
                //$resultArr['product_error'][$orderData['Klant (order)']][$orderData['EAN ( EAN nummer MPS)']] = $e->getMessage();

            }
        }


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

