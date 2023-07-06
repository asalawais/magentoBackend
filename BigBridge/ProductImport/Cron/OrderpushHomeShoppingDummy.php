<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;

use BigBridge\ProductImport\Helper\RestCurlClientConfig;

/**
 * Class ProductPublisher
 */
class OrderpushHomeShoppingDummy //implements CronJobInterface
{

    private $_restCurlClient;
    private $_orderModel;
    protected $logger;
    protected $_orderCollectionFactory;
    protected $timezone;
    protected $config;
    protected $_productRepository;
    private $sourceItemsBySku;
    private $productRepositoryInterface;

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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Sales\Model\Order $orderModel
    ) {
        $this->_productRepository = $productRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->_orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->productRepositoryInterface = $productRepositoryInterface;
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
    public function loadProductBySku($sku)
    {
        return $this->productRepositoryInterface->get($sku);
    }

    public function getSourceItemBySku($sku)
    {
       return $this->sourceItemsBySku->execute($sku);
    }

    public function getAfasUnit($id)
    {
        $unit = 3;
        $aGroep = 8010;
        $pWarehouse = '';
        $product = $this->_productRepository->getById($id);
        if($Artikelgroep = $product->getCustomAttribute('Artikelgroep')) {
           $aGroep = $Artikelgroep->getValue();
        }
        if($PreferredWarehouse = $product->getCustomAttribute('preferred_warehouse')){
           $pWarehouse = $PreferredWarehouse->getValue();
        }
        if($aGroep==8020 && $pWarehouse=='PML Text Drops.'){
            $unit = 2;
        }
        return $unit;
    }

    public function getOrders()
   {
       $from = date("Y-m-d 00:00:00",strtotime('2022-7-1')); // start date
       $to = date("Y-m-d 23:59:59", strtotime('2022-7-10')); // end date
       $collection = $this->_orderCollectionFactory->create()
           ->addAttributeToSelect('*')
           ->addFieldToFilter('status', 'complete')
           ->addFieldToFilter('state', 'complete')
           ->addFieldToFilter('order_dummy', 0)
           ->addFieldToFilter('store_id', ['in'=>[1,3,5]])
           //->addFieldToFilter('order_ref_nbr', ["neq" => 'NULL'])
           ->addFieldToFilter('afas_order_nbr', ["neq" => 'NULL'])
           //->addFieldToFilter('mollie_transaction_id', ["neq" => 'NULL'])
           ->addFieldToFilter('created_at', ['gteq' => $from])
           ->addFieldToFilter('created_at', ['lteq' => $to]);

       //$collection->getSelect()->limit(5,0);
       //echo count($collection);
       //die;
       return $collection;
    }


    private function getClient(){

       $this->logger->debug('OrderPush HomeShopping: start');
        $client = $this->_restCurlClient->getClient();
        $OrderData = [];
        $this->logger->debug('OrderPush HomeShopping: start count'.count($this->getOrders()));
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
                //$OrderData['Fields']['Unit'] = $orderProducts['unit'];
               $FbSales = $this->setFbSales($OrderData, [], $sourceKey);
               //echo $FbSales;
               $this->logger->critical('OrderPush HomeShopping:'.$FbSales);
               $suc = $client->callAfas('POST', 'connectors/FbSales', [], $FbSales);
               $dataResult = json_decode($suc);
               $this->logger->critical('OrderPush HomeShopping: result'.$suc);
               $afasOrder[] = $dataResult->results->FbSales->OrNu;

        }
             $orderN = $this->getOrder($order->getRealOrderId());
            //$this->logger->debug('OrderPush: $orderN->getId()'.$orderN->getId());
              if($orderN->getId() && !empty($afasOrder)){
               $orderN->setData('afas_order_nbr_dummy', implode(",",$afasOrder));
               $orderN->setData('order_dummy',1);
               //$orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
               //$orderN->setState($orderState)->setStatus($orderState);
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
            $orderInfo['channelId'] = $order->getChannelId();
             $tracksCollection = $order->getTracksCollection();
             foreach ($tracksCollection->getItems() as $track) {
                 $trackNumbers[] = $track->getTrackNumber();
             }

            $orderInfo['tracking'] = $trackNumbers[0];
            $orderInfo['RfCs'] = $order->getChannelName();
            $orderInfo['debtorId'] = $orderRef->getData('debtor_id');
            //$next10daysTimeZone = $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d H:i:s');
            //$orderInfo['next10days'] = date('Y-m-d', strtotime($next10daysTimeZone. '+10 weekdays'));


            return $orderInfo;

 }

 public function getOrderItems($order, $products){

            $orderSubtotal = ($order->getSubtotal()/1.21);
            foreach ($order->getAllVisibleItems() as $item)
            {
               $data['Fields'] = array(
                                    "ItCd" => 60010277,
                                    "QuUn" => 1,
                                    "VaIt" => 2,
                                    "Upri" => number_format((float)$orderSubtotal , 2, '.', ''),
                                    "BuUn" => "STK"
                                  );
                $sourceCode = 'default';

               //$sourceCode = 'default';
               $products['Element'][$sourceCode][] = $data;

          }
            return array('products' => $products);

 }
 public function setFbSales($data, $dataAfas, $sourceKey){
     $countryCode = $data['Fields']['countryId'];
     $country['BE'] = 'B';
     $country['DE'] = 'D';
     $country['NL'] = 'NL';
     $country['FR'] = 'F';
     //if($countryCode == 'DE' || $countryCode == 'BE' ) {
         $countryCode = $country[$countryCode];
     //}
      $dataAfas['Element']['Fields'] = [

                    'RfCs' => $data['Fields']['RfCs'].' / '.$countryCode,
                    'U69281111456C5BCE54EB75BAF6D5304F' => $data['Fields']['orderReference'],
                    'OrDa' => $data['Fields']['dateTimeZone'],
                    //'DaDe' => $data['Fields']['next10days'],
                    'DbId' => 10277,
                    'VaDu'=> 1,
                    'CuId' => 'EUR',
                    'Unit' => 6,
                    'War'  => $this->getWarehouse($sourceKey),
                    'U6067C76D445518C3C7143BE070C8F107' => $data['Fields']['shipName'],
                    'UCC40DB5C4721EEB3497F9FBE4BEF7EB7' => $data['Fields']['street'][0],
                    'U4481B51A4A9DA871BFB62A95A1BB60C5' => $data['Fields']['postCode'],
                    'U43B1C86A417B7545ED74358498FB381A' => $data['Fields']['city'],
                    'UFEB7984F4083148310C1F9B0D8D32B6F' => $countryCode,
                    'UE738CB4A44F1AC8CEEC19E969581BC00' => $data['Fields']['email'],
                    'U2CC6C618409EDE64D2B5C6A869745B8E' => $data['Fields']['telephone'],
                    'U1E8F40DB426F95062B50D29D5C300154' => "",
                    'U2763E2004660BC85D24EF49A1BF586A8' => "Complete",
                    'U4949642146F96C50B8900BBD2247E587' => "HomeShopping24",
                    'U5E210D8E41377C0448CFFFABEF9A9959' => $data['Fields']['channelId'],  // Channel id
                    'U1894E7EC43E51449CDEF17ABAA571DD5' => $data['Fields']['tracking']  // tracking

      ];
      $dataAfas['Element']['Objects'] = [
        'FbSalesLines' => $data['Objects']
      ];

      $fbSales['FbSales'][] = $dataAfas;
      return json_encode($fbSales);


 }

      public function getWarehouse($war){

          /*$warehouses = [
            'default'          => 'PML Text Drops.'
          ];*/
          $warehouses = [
              'default'          => 'PML Text Drops.',
              'pml_text_drops'=>'PML Text Drops.',
              'pml'=>'PML'
          ];
          return $warehouses[$war];

      }


}
