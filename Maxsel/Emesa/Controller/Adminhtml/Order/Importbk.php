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
use Psr\Log\LoggerInterface;
use Maxsel\Emesa\Helper\Connect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Maxsel\Emesa\Helper\OrderCreate;
use Maxsel\Emesa\Helper\Order as ExtendedOrder;
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
            $orderList = $this->connect->listOrders();
            if($orderList->getTotal() > 0) {
                $result = $this->orderProcess($orderList);
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

    public function orderProcess(\Emesa\PartnerPlatform\Model\OrderList $ordersList){
        $this->logger->debug('OrderNew: start');
        $orderReferences = [];
        foreach ($ordersList->getData() as $orderDataN) {
            try {
                $productsList = array();
                $codAdded = 0;
                foreach ($orderDataN['lines'] as $orderLines) {
                    $orderLines->supplierProductId = '8717752016440';
                    $productsList[] = [
                        'product_id' => $this->getProductBySku($orderLines->supplierProductId)->getEntityId(),
                        'qty' => $orderLines->quantity,
                        'price' => $orderLines->unitPriceInclVatInCents
                    ];
                }

                //$orderDataN['totalShippingCostsInclVatInCents'];

                //$orderDataN['address']->digiCode;

               $ordRef = $this->getOrderRef($orderDataN['marketOrderId']);
                if (count($ordRef) === 0) {
                    $tempOrder = [
                        'currency_id' => 'EUR',
                        /** @var type $orderData */
                        'email' => $orderDataN['customerEmail'], //buyer email id
                        'date' => $orderDataN['orderDateTime'],
                        'ref' => $orderDataN['marketOrderId'],
                        'note' => $orderDataN['deliveryInstructions'],
                        'shipping_address' => [
                            'firstname' =>  $orderDataN['address']->name, //address Details
                            'lastname' =>  $orderDataN['address']->name,
                            'company' => $orderDataN['address']->company,
                            'street' => $orderDataN['address']->street . ' ' . $orderDataN['address']->houseNumber. ' ' . $orderDataN['address']->houseNumberAddition.' '.$orderDataN['address']->floorNumber,

                            'city' => $orderDataN['address']->city,
                            'country_id' => $orderDataN['address']->countryIso3166,
                            'region' => '',
                            'postcode' => $orderDataN['address']->zipcode,
                            'telephone' => $orderDataN['customerPhoneNumber'],
                            'fax' => '',
                            'save_in_address_book' => 1
                        ],
                        'items' => $productsList
                    ];

//print_r($tempOrder);

                    //$this->logger->debug('OrderNew: start'. print_($tempOrder, true));
                    $result = $this->orderCreate->createMageOrder($tempOrder);
                    $result = $this->newOrder->importOrder($tempOrder, 1);
                    $resultArr[] = $result;
                    $resultArr['order_success'][] = $result;
                    $orderResponse[] = $result;

                    //$this->logger->debug($result);

                } else {
                    //same order reference found
                    $resultArr['reference_error'][] = $orderDataN['marketOrderId'];
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

