<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;

use BigBridge\ProductImport\Helper\RestCurlClientConfig;
use BigBridge\ProductImport\Helper\Order as OrderHelper;
use BigBridge\ProductImport\Helper\CurlFetch;
/**
 * Class ProductPublisher
 */
class OrderpushNaduvi
{

    private $_restCurlClient;
    private $_orderModel;
    protected $logger;
    protected $_orderCollectionFactory;
    protected $timezone;
    protected $config;
    protected $_productRepository;
    private $sourceItemsBySku;
    private $orderHelper;
    private $curlHelper;

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
        OrderHelper $orderHelper,
        CurlFetch $curlHelper,
        \Magento\Sales\Model\Order $orderModel
    ) {
        $this->_productRepository = $productRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->_orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->logger = $logger;
         $this->orderHelper = $orderHelper;
         $this->curlHelper = $curlHelper;
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
    public function getPreferredWareHouse($id)
    {
        $product = $this->_productRepository->getById($id);
        if ($PreferredWarehouse = $product->getCustomAttribute('preferred_warehouse')) {
            return $PreferredWarehouse->getValue();
        }
    }

    public function getTypeItem($id)
    {   $typeItemValue = 2;
        $product = $this->_productRepository->getById($id);
        if ($typeItem = $product->getCustomAttribute('type_item')) {
            return $typeItem->getValue();
        }
        return $typeItemValue;
    }

    public function getAfasUnit($id)
    {
        $unit = 3;
        $product = $this->_productRepository->getById($id);
        $Artikelgroep = $product->getCustomAttribute('Artikelgroep')->getValue();
        $PreferredWarehouse = $product->getCustomAttribute('preferred_warehouse')->getValue();
        if($Artikelgroep==8020 && $PreferredWarehouse=='Brouwers Text'){
            $unit = 2;
        }
        return $unit;
    }

    public function getOrders()
   {
       $collection = $this->_orderCollectionFactory->create()
       ->addAttributeToSelect('*')
           ->addFieldToFilter('status', 'processing')
           ->addFieldToFilter('entity_id', array("gt" => 15166)) //for testing
           ->addFieldToFilter('state', 'processing')
       //->addFieldToFilter('channable_id', array("null" => true))
       //->addFieldToFilter('channel_id', array("null" => true))
       //->addFieldToFilter('channel_name', 'naduvi')
       ->addFieldToFilter('store_id', 6)
       //->addFieldToFilter('order_ref_nbr', array("neq" => 'NULL'))
       ->addFieldToFilter('afas_order_nbr', array("null" => true))
       ->setOrder(
                'created_at',
                'asc'
            );
       //echo count($collection);
       return $collection;
    }


    private function getClient(){

       $this->logger->debug('OrderPush naduvi: start');
        $client = $this->_restCurlClient->getClient();
        $OrderData = [];
        $this->logger->debug('OrderPush Naduvi: start count'.count($this->getOrders()));
        foreach ($this->getOrders() as $order) {
            try {
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
                $OrderData['Fields']['Unit'] = $orderProducts['unit'];
               $FbSales = $this->setFbSales($OrderData, [], $sourceKey);
               //echo $FbSales;
               $this->logger->critical('OrderPush Naduvi:'.$FbSales);
               $suc = $client->callAfas('POST', 'connectors/FbSales', [], $FbSales);
               $dataResult = json_decode($suc);
               $this->logger->critical('OrderPush Naduvi: result'.$suc);
                $guidResult = $this->curlHelper->getGUIDByAfasOrderId($dataResult->results->FbSales->OrNu);
                $guidData = [];
                foreach($guidResult['rows'] as $guidItem){
                    $guidData[$guidItem['Itemcode']] = $guidItem['GUID'];
                }
                foreach ($OrderData['Objects']['Element'] as $key => $line){
                    $line['Fields']['SoGu'] = $guidData[$line['Fields']['ItCd']];
                    $line['Fields']['SoOr'] = $dataResult->results->FbSales->OrNu;
                    $OrderData['Objects']['Element'][$key] = $line;
                }
                $trackNumbers = [];
                $tracksCollection = $order->getTracksCollection();
                foreach ($tracksCollection->getItems() as $track) {
                    $trackNumbers[] = $track->getTrackNumber();
                }
                if(!empty($trackNumbers)) {
                    $OrderData['packing_trace'] = $trackNumbers[0];
                }
                else{
                    $OrderData['packing_trace'] = '';
                }
                $this->logger->critical('OrderPush channable: GUID result' . print_r($guidResult, true));
                $OrderData['packing_time'] = $this->timezone->date(new \DateTime())->format('Y-m-d');
                $packingSlipResult = $this->curlHelper->getPackingSlip($dataResult->results->FbSales->OrNu, $OrderData, 10329);
                $packingSlips[] = $packingSlipResult['results']['FbDeliveryNote']['OrNu'];
                $this->logger->critical('OrderPush channable: result' . print_r($packingSlips, true));

                $afasOrder[] = $dataResult->results->FbSales->OrNu;

        }
             $orderN = $this->getOrder($order->getRealOrderId());
            //$this->logger->debug('OrderPush: $orderN->getId()'.$orderN->getId());
              if($orderN->getId() && !empty($afasOrder)){
               $orderN->setData('afas_order_nbr', implode(",",$afasOrder));
                  $orderN->setData('afas_packing_slip_nbr', implode(",", $packingSlips));
               $orderN->setData('afas_order_status',"I");
               $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
               $orderN->setState($orderState)->setStatus($orderState);
               $orderN->save();
                  // save sales grid data
                  $this->orderHelper->salesGridDataSave($orderN);
              }
        }
    catch (\Exception $e){
            $this->logger->critical('OrderPush Naduvi: '.$e->getMessage());
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
            $trackNumbers = [];
             $tracksCollection = $order->getTracksCollection();
             foreach ($tracksCollection->getItems() as $track) {
                 $trackNumbers[] = $track->getTrackNumber();
             }
             if(!empty($trackNumbers)) {
                 $orderInfo['tracking'] = $trackNumbers[0];
             }
             else{
                 $orderInfo['tracking'] = '';
             }

            //$orderInfo['tracking'] = $trackNumbers[0];
            $orderInfo['RfCs'] = 'Naduvi';
            $orderInfo['debtorId'] = $orderRef->getData('debtor_id');
            //$next10daysTimeZone = $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d H:i:s');
            //$orderInfo['next10days'] = date('Y-m-d', strtotime($next10daysTimeZone. '+10 weekdays'));


            return $orderInfo;

 }

 public function getOrderItems($order, $products){


            foreach ($order->getAllVisibleItems() as $item)
            {
               $data['Fields'] = array(
                                    "ItCd" => $item->getSku(),
                                    "QuUn" => $item->getQtyOrdered(),
                                    "VaIt" => $this->getTypeItem($item->getProductId()),
                                    "BuUn" => "STK"
                                  );

                $sourceCode = 'default';
                $sourceItemList = $this->getSourceItemBySku($item->getSku());
                $unit = $this->getAfasUnit($item->getProductId());
                foreach ($sourceItemList as $source) {
                    if($source->getSourceCode()!='default') {
                        $sourceCode = $source->getSourceCode();
                        break;
                    }
                }
                $sourceCode = $this->getPreferredWareHouse($item->getProductId());
                $products['Element'][$sourceCode][] = $data;
                $products['unit'] = $unit;

          }
            return array('products' => $products);

 }
 public function setFbSales($data, $dataAfas, $sourceKey){
      $PrId = '';
      //$data['Fields']['debtorId'] = 10264;
     $countryCode = $data['Fields']['countryId'];
     $country['BE'] = 'B';
     $country['DE'] = 'D';
     $country['NL'] = 'NL';
     $country['FR'] = 'F';
     if($countryCode == 'DE' || $countryCode == 'BE' || $countryCode == 'FR' ) {
         $countryCode = $country[$countryCode];
     }
      $dataAfas['Element']['Fields'] = [

                    'RfCs' => $data['Fields']['RfCs'].' / '.$countryCode,
                    'U69281111456C5BCE54EB75BAF6D5304F' => $data['Fields']['orderReference'],
                    'OrDa' => $data['Fields']['dateTimeZone'],
                    //'DaDe' => $data['Fields']['next10days'],
                    'DbId' => 10329,
                    'VaDu'=> 6,
                    'CuId' => 'EUR',
                    'Unit' => $data['Fields']['Unit'],
                    'War'  => $sourceKey,
                    'PrId' => $PrId,
                    'U6067C76D445518C3C7143BE070C8F107' => $data['Fields']['shipName'],
                    'UCC40DB5C4721EEB3497F9FBE4BEF7EB7' => $data['Fields']['street'][0] . ' '. $data['Fields']['street'][1] ?? ''. ' ' . $data['Fields']['street'][2] ?? '',
                    'U4481B51A4A9DA871BFB62A95A1BB60C5' => $data['Fields']['postCode'],
                    'U43B1C86A417B7545ED74358498FB381A' => $data['Fields']['city'],
                    'UFEB7984F4083148310C1F9B0D8D32B6F' => $countryCode,
                    'UE738CB4A44F1AC8CEEC19E969581BC00' => $data['Fields']['email'],
                    'U2CC6C618409EDE64D2B5C6A869745B8E' => $data['Fields']['telephone'],
                    'U1E8F40DB426F95062B50D29D5C300154' => "",
                    'U2763E2004660BC85D24EF49A1BF586A8' => "Complete",
                    'U4949642146F96C50B8900BBD2247E587' => "naduvi",
                    'U5E210D8E41377C0448CFFFABEF9A9959' => 'Naduvi',  // Channel id
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
              'default'          => 'Brouwers Text',
              'brouwers_text'=>'Brouwers Text',
              'ommen'=>'Ommen'
          ];
          return $warehouses[$war];

      }


}
