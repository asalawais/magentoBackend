<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\Groupon\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Maxsel\Groupon\Helper\Connect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Maxsel\Groupon\Helper\OrderCreate;
use Maxsel\Groupon\Helper\Order as ExtendedOrder;
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
            //$api = new \Netcrash\Groupon\apiClient();
            //$api->setToken("1mnxi4oqhmdTMkUowoMgPDq7Kl2eHiu");
            $result = [];
            $response = file_get_contents('https://scm.commerceinterface.com/api/v4/get_orders?supplier_id=43674&token=r8kRQQia8CDUtVkuP9kv0df4IGEyqdW');
            //print_r($response);
            if ($response) {
                $response_json = json_decode($response);
                if ($response_json->success == true) {
                    $meta = $response_json->meta;
                    if($meta->no_of_items > 0) {
                     $result = $this->orderProcess($response_json->data);
                     print_r($result);
                     $this->markExported($result['ci_lineitemid']);
                     $result['orders'] = $meta->no_of_items;
                    }
                } else {
                    //Alarm!
                }
            }
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

    public function markExported($ciLineitemIds){
        // requires PHP cURL http://no.php.net/curl
        $datatopost = array (
            "supplier_id" => "43674",
            "token" => "r8kRQQia8CDUtVkuP9kv0df4IGEyqdW",
            "ci_lineitem_ids" => json_encode ( $ciLineitemIds ),
        );
        $ch = curl_init ("https://scm.commerceinterface.com/api/v4/mark_exported");
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        if( $response ) {
            $response_json = json_decode( $response );
            if( $response_json->success == true ) {
                //Successfully marked as exported (only items which are not already marked exported
            } else {

            }
        }
    }

    public function orderProcess($ordersList){
        $this->logger->debug('OrderNew: start');
        $orderReferences = [];
        $resultArr = [];
        $ci_lineitemid = [];
        foreach ($ordersList as $orderDataN) {
            try {

                $productsList = array();
                $codAdded = 0;
                foreach ($orderDataN->line_items as $orderLines) {
                    $ci_lineitemid[] = $orderLines->ci_lineitemid;
                    $productsList[] = [
                        'id' => $this->getProductBySku($orderLines->sku)->getEntityId(),
                        'quantity' => $orderLines->quantity,
                        'price' => $orderLines->unit_price,
                        'title'           => $orderLines->name,
                        'ean'             => $orderLines->sku,
                        'delivery_period' => ''
                    ];
                    //$this->logger->debug('$productsList: '. print_r($productsList, true));
                }
                //$orderDataN->setCustomerEmail('almrsystems@gmail.com');
                $email =  $orderDataN->orderid.'@mpstextiles.com';
                $ordRef = $this->getOrderRef($orderDataN->orderid);
                if ($email!='' && count($ordRef) === 0) {
                    $billing = $orderDataN->customer->billing_address;
                    $tempOrder = [
                        'currency_id' => 'EUR',
                        'email' => $email, //buyer email id
                        'date' => $orderDataN->date,
                        'ref' => $orderDataN->orderid,
                        'note' => $orderDataN->fulfillment_method,
                        'order_status' => 'new',
                       // 'emesa_id' => $orderDataN->getMarketOrderId(),
                        'channel_name' => 'groupon',
                        'channel_id' => $orderDataN->orderid,
                        'channable_channel_label' => 'Groupon',
                        'customer' => [
                            'first_name' => $billing->name,
                            'middle_name' => '',
                            'last_name' => $billing->name,
                            'email' => $email,
                            'mobile' => $orderDataN->customer->phone,
                            'phone' => $orderDataN->customer->phone,

                        ],
                        'shipping' => [
                            'first_name' =>  $billing->name, //address Details
                            'middle_name' => '',
                            'last_name' =>  $billing->name,
                            'company' => '',
                            'street' => $billing->address1,
                            'house_number' => '',
                            'house_number_ext' => $billing->address2,
                            'city' => $billing->city,
                            'country_code' => $billing->country,
                            'state_code' => '',
                            'zip_code' => $billing->zip,
                            'telephone' => $orderDataN->customer->phone,
                            'email'       => $email,
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'billing' => [
                            'first_name' =>  $billing->name, //address Details
                            'middle_name' => '',
                            'last_name' =>  $billing->name,
                            'company' => '',
                            'street' => $billing->address1,
                            'house_number' => '',
                            'house_number_ext' => $billing->address2,
                            'city' => $billing->city,
                            'country_code' => $billing->country,
                            'state_code' => '',
                            'zip_code' => $billing->zip,
                            'telephone' => $orderDataN->customer->phone,
                            'email'       => $email,
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'price' => [
                            'shipping' => $orderDataN->amount->shipping,
                            'currency' => 'EUR'
                        ],
                        'products' => $productsList
                    ];

                    //$result = $this->orderCreate->createMageOrder($tempOrder);
                    $this->logger->debug('$tempOrder: '.print_r($tempOrder, true));
                    //print_r($tempOrder);
                    //$result = [];
                    $result = $this->newOrder->importOrder($tempOrder, 3);
                    //print_r($result);
                    //$resultArr[] = $result;
                    $resultArr['order_success'][] = $result;
                    //$ci_lineitemid = array('797979999', '454545454');
                    $resultArr['ci_lineitemid'] = $ci_lineitemid;
                    $orderResponse[] = $result;

                } else {
                    //same order reference found
                    if($email==''){
                        $this->logger->debug('OrderId: '. $orderDataN->orderid. ' email not found');
                    }
                    else{
                        $this->logger->debug('OrderId: '. $orderDataN->orderid. ' already exist');
                    }
                    $resultArr['reference_error'][] = $orderDataN->orderid;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());

            }
        }
        return $resultArr;
    }
    public function getOrderRef($ref)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_ref_nbr', $ref)
            ->addFieldToFilter('store_id', 3);
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

