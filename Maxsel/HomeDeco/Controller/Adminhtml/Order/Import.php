<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\HomeDeco\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Maxsel\HomeDeco\Helper\Connect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Maxsel\HomeDeco\Helper\OrderCreate;
use Maxsel\HomeDeco\Helper\Order as ExtendedOrder;
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
             * @var \Picqer\BolPlazaClient\BolPlazaClient $client
             */
            $result = [];
            $public_key = 'odwRwbtljujRUouYIXouDLLybLjMzPjn';
            $private_key = 'wmBvmhoGPXfoVQpMUmlBzDosLLYrRrSDvumXFvfBATcjeoydkDFhPUXYUqPxOxJAdYkunKDQpPxDsjyUojidTjIzMqGvLMymJfOJnaoWDxHXIFqgldDzqoxEIZNWOcGwknfJHfiWoINJYMfSSbELQGuXkLBkwEnKXJXwRXJrJopvBQqkVKSJlbInHObMTPjYaGsRtMPumhYQICkSVhFfHKAAibmbVQLJzvlTpTKSKSRxUfalLIACTLDHnAToTrlv';


            $client = new \Picqer\BolPlazaClient\BolPlazaClient($public_key, $private_key);
            //print_r($client->getOrders());

            if(count($client->getOrders()) > 0) {
                foreach ($client->getOrders() as $order) {
                     $this->orderProcess($order);
                }
            }
            $result['orders'] = count($client->getOrders());
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

    public function orderProcess(\Picqer\BolPlazaClient\Entities\BolPlazaOrder $orderDataN){
        $this->logger->debug('OrderNew: start');
        $orderReferences = [];
        try {
            $productsList = array();
            foreach ($orderDataN->OrderItems as $orderLines) {
                $productsList[] = [
                    'id' => $this->getProductBySku($orderLines->EAN)->getEntityId(),
                    'quantity' => $orderLines->Quantity,
                    'price' => $this->getProductBySku($orderLines->EAN)->getPrice(),
                    'title'           => $orderLines->Title,
                    'ean'             => $orderLines->EAN,
                    'delivery_period' => ''
                ];
            }

            //$orderDataN['totalShippingCostsInclVatInCents'];

            //$orderDataN['address']->digiCode;

            $customerDetails = $orderDataN->CustomerDetails;
            $shipmentDetail = $customerDetails->ShipmentDetails;
            $billingDetail = $customerDetails->BillingDetails;

            //$orderDataN['customerEmail'] = 'almrsystems@gmail.com';

            $ordRef = $this->getOrderRef('hd-'.$orderDataN->OrderId);
            $storeId = 1;
            if($storeId==1){
                $channelName = 'homedeco';
                $channelLabel = 'homedeco';
                $shippingPrice = 8.45;
            }

            if (count($ordRef) === 0) {
                $tempOrder = [
                    'currency_id' => 'EUR',

                    'email' => $billingDetail->Email, //buyer email id
                    'date' => '',
                    'ref' => 'hd-'.$orderDataN->OrderId,
                    'note' => '',
                    'order_status' => 'new',
                    'channel_name' => $channelName,
                    'channel_id' => '',
                    'channable_channel_label' => $channelLabel,
                    'customer' => [
                        'first_name' => $billingDetail->Firstname,
                        'middle_name' => '',
                        'last_name' => $billingDetail->Surname,
                        'email' => $billingDetail->Email,
                        'mobile' => $billingDetail->Phone,
                        'phone' => $billingDetail->Phone,

                    ],

                    'shipping' => [
                        'first_name' =>  $shipmentDetail->Firstname, //address Details
                        'middle_name' => '',
                        'last_name' =>  $shipmentDetail->Surname,
                        'company' => $shipmentDetail->Company,
                        'street' => $shipmentDetail->Streetname,
                        'house_number' => $shipmentDetail->Housenumber,
                        'house_number_ext' => $shipmentDetail->HousenumberExtended,
                        'city' => $shipmentDetail->City,
                        'country_code' => $shipmentDetail->CountryCode,
                        'state_code' => '',
                        'zip_code' => $shipmentDetail->Zipcode,
                        'telephone' => $shipmentDetail->Phoner,
                        'email'       => $shipmentDetail->Email,
                        'fax' => '',
                        'save_in_address_book' => 1
                    ],
                    'billing' => [
                        'first_name' =>  $billingDetail->Firstname, //address Details
                        'middle_name' => '',
                        'last_name' =>  $billingDetail->Surname,
                        'company' => $billingDetail->Company,
                        'street' => $billingDetail->Streetname,
                        'house_number' => $billingDetail->Housenumber,
                        'house_number_ext' => $billingDetail->HousenumberExtended,
                        'city' => $billingDetail->City,
                        'country_code' => $billingDetail->CountryCode,
                        'state_code' => '',
                        'zip_code' => $billingDetail->Zipcode,
                        'telephone' => $billingDetail->Phone,
                        'email'       => $billingDetail->Email,
                        'fax' => '',
                        'save_in_address_book' => 1
                    ],
                    'price' => [
                        'shipping' => $shippingPrice,
                        'currency' => 'EUR'
                    ],
                    'products' => $productsList
                ];

                //$result = $this->orderCreate->createMageOrder($tempOrder);
                //print_r($tempOrder);
                $result = $this->newOrder->importOrder($tempOrder, $storeId);
                $resultArr[] = $result;
                $resultArr['order_success'][] = $result;
                $orderResponse[] = $result;

            } else {
                //same order reference found
                $this->logger->info('referenceOrder: '. 'hd-'.$orderDataN->OrderId. ' already exist');
                $resultArr['reference_error'][] = 'hd-'.$orderDataN->OrderId;
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
    public function getOrderRef($ref)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_ref_nbr', $ref)
            ->addFieldToFilter('channel_name', 'homedeco');
        echo count($collection);
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

