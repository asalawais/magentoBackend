<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace Maxsel\HomeDeco\Helper;

//use BigBridge\ProductImport\System\ConfigInterface;

use BigBridge\ProductImport\Helper\RestCurlClientConfig;
use PracticalAfas\Client\RestCurlClient;

/**
 * Class ProductPublisher
 */
class OrderPush //implements CronJobInterface
{

    private $_restCurlClient;
    private $_orderModel;
    protected $logger;
    protected $_orderCollectionFactory;
    protected $timezone;
    protected $config;
    protected $_productRepository;
    private $sourceItemsBySku;

    /**
     * ProductPublisher constructor.
     *
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ProductRepositoryFactory $repositoryFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */


     public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        RestCurlClientConfig $restCurlClient,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\Order $orderModel
    ) {
        $this->_productRepository = $productRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->_orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->_restCurlClient = $restCurlClient;


    }


    /**
     * Publish products
     *
     * @return void
     */
    public function execute()
    {
         $this->getClient();

    }

    private function getOrder($id){
       $order = $this->_orderModel;
       return $order->loadByIncrementId($id);
    }

    public function getProductById($id){
      return $this->_productRepository->getById($id);
    }

    public function getSourceItemBySku($sku)
    {
       return $this->sourceItemsBySku->execute($sku);
    }

    public function getOrders()
   {
       $collection = $this->_orderCollectionFactory->create()
       ->addAttributeToSelect('*')
       ->addFieldToFilter('status', 'processing')
       //->addFieldToFilter('entity_id', array("eq" => 12)) //for testing
       ->addFieldToFilter('state', 'processing')
       //->addFieldToFilter('channable_id', array("eq" => 'NULL'))
       //->addFieldToFilter('channel_id', array("neq" => 'NULL'))
       ->addFieldToFilter('channel_name', 'emesa')
       //->addFieldToFilter('order_ref_nbr', array("neq" => 'NULL'))
       ->addFieldToFilter('afas_order_nbr', array("null" => true))
       ->setOrder(
                'created_at',
                'desc'
            );
       //echo count($collection);
       return $collection;
    }


    private function getClient(){

       $this->logger->debug('Emesa OrderPush: start');
        $client = $this->_restCurlClient->getClient();
        $OrderData = [];
        $this->logger->debug('Emesa OrderPush: start count'.count($this->getOrders()));
        foreach ($this->getOrders() as $order) {
            $orderInfo = $this->getOrderInfo($order, []);
           //Loop through each item and fetch data
            $orderItems = $this->getOrderItems($order, []);
            $orderProducts = $orderItems['products'];
            $OrderData['Fields'] = $orderInfo;
            $OrderData['Objects'] =  $orderProducts;
//print_r($OrderData);
            $afasOrder = [];
            foreach ($OrderData['Objects']['Element'] as $sourceKey => $items) {
               $OrderData['Objects']['Element'] = $items;
               $FbSales = $this->setFbSales($OrderData, [], $sourceKey);
               echo $FbSales;
               $this->logger->debug('OrderPush:'.$FbSales);
               $suc = $this->restCurlClient()->callAfas('POST', 'connectors/FbSales', [], $FbSales);
               $dataResult = json_decode($suc);
               $this->logger->debug('OrderPush: result'.$suc);
               $afasOrder[] = $dataResult->results->FbSales->OrNu;

        }
             $orderN = $this->getOrder($order->getRealOrderId());
            //$this->logger->debug('OrderPush: $orderN->getId()'.$orderN->getId());
              if($orderN->getId() && !empty($afasOrder)){
               $orderN->setData('afas_order_nbr', implode(",",$afasOrder));
               $orderN->setData('afas_order_status',"I");
               $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
               $orderN->setState($orderState)->setStatus($orderState);
               $orderN->save();
              }


     }

 }


 public function getOrderInfo($order, $orderInfo){

            $orderRef = $this->getOrder($order->getRealOrderId());
            $orderInfo['total'] = $orderRef->getData('order_cod'); //$order->getGrandTotal();
            $orderInfo['name'] = $order->getBillingAddress()->getFirstname().' '.$order->getBillingAddress()->getLastname();
            $orderInfo['shipName'] = $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
            $orderInfo['street'] = $order->getShippingAddress()->getStreet();
            $orderInfo['postCode'] = $order->getShippingAddress()->getPostcode();
            $orderInfo['city'] = $order->getShippingAddress()->getCity();
            $orderInfo['countryId'] = $order->getShippingAddress()->getCountryId();
            $orderInfo['email'] = $order->getShippingAddress()->getEmail();
            $orderInfo['telephone'] = $order->getShippingAddress()->getTelephone();
            $orderInfo['dateTimeZone'] = $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d');



            $orderInfo['orderReference'] = $order->getRealOrderId();
            $orderInfo['RfCs'] = $orderRef->getData('order_ref_nbr');
            $orderInfo['debtorId'] = $orderRef->getData('debtor_id');
            $next10daysTimeZone = $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d H:i:s');
            $orderInfo['next10days'] = date('Y-m-d', strtotime($next10daysTimeZone. '+10 weekdays'));


            return $orderInfo;

 }

 public function getOrderItems($order, $products){


            foreach ($order->getAllVisibleItems() as $item)
            {
               $data['Fields'] = array(
                                    "ItCd" => $item->getSku(),
                                    "QuUn" => $item->getQtyOrdered(),
                                    "VaIt" => 2,
                                    "BuUn" => "STK"
                                  );

               $sourceCode = 'default';
               $products['Element'][$sourceCode][] = $data;

          }
            return array('products' => $products);

 }
 public function setFbSales($data, $dataAfas, $sourceKey){
      $PrId = '';
      if($data['Fields']['debtorId']==10264){
          $PrId = 'BB';
      }
      //$data['Fields']['debtorId'] = 10264;
     $countryCode = $data['Fields']['countryId'];
     $country['BE'] = 'B';
     $country['DE'] = 'D';
     $country['NL'] = 'NL';
     if($countryCode == 'DE' || $countryCode == 'BE' ) {
         $countryCode = $country[$countryCode];
     }
      $dataAfas['Element']['Fields'] = [

                    'RfCs' => $data['Fields']['RfCs'],
                    'U69281111456C5BCE54EB75BAF6D5304F' => $data['Fields']['orderReference'],
                    'OrDa' => $data['Fields']['dateTimeZone'],
                    'DaDe' => $data['Fields']['next10days'],
                    'DbId' => 10131,
                    'VaDu'=> 6,
                    'CuId' => 'EUR',
                    'Unit' => 2,
                    'War'  => $this->getWarehouse($sourceKey),
                    'PrId' => $PrId,
                    'U6067C76D445518C3C7143BE070C8F107' => $data['Fields']['shipName'],
                    'UCC40DB5C4721EEB3497F9FBE4BEF7EB7' => $data['Fields']['street'][0],
                    'U4481B51A4A9DA871BFB62A95A1BB60C5' => $data['Fields']['postCode'],
                    'U43B1C86A417B7545ED74358498FB381A' => $data['Fields']['city'],
                    'UFEB7984F4083148310C1F9B0D8D32B6F' => $countryCode,
                    'UE738CB4A44F1AC8CEEC19E969581BC00' => $data['Fields']['email'],
                    'U2CC6C618409EDE64D2B5C6A869745B8E' => $data['Fields']['telephone'],
                    'U1E8F40DB426F95062B50D29D5C300154' => $data['Fields']['total'],
                    'U2763E2004660BC85D24EF49A1BF586A8' => "Complete",
                    'U4949642146F96C50B8900BBD2247E587' => "BOL",
                    'U5E210D8E41377C0448CFFFABEF9A9959' => "1259952155",
                    'U1894E7EC43E51449CDEF17ABAA571DD5' => "05162083733343"

      ];
      $dataAfas['Element']['Objects'] = [
        'FbSalesLines' => $data['Objects']
      ];

      $fbSales['FbSales'][] = $dataAfas;
      return json_encode($fbSales);


 }

      public function getWarehouse($war){

          $warehouses = [
            'default'          => 'PML Text Drops.'
          ];
          return $warehouses[$war];

      }

    public function restCurlClient(){

        return new RestCurlClient(
            [ 'customerId' => 89626, 'appToken' => $this->getAfasToken(), 'environment'=>'test'] );

    }

    private function getAfasToken(){
        return '84D03562890448F5B5AAF108D3FA212F1A604E0F4EA4AD2A6176E5B35781FC08';
    }


}
