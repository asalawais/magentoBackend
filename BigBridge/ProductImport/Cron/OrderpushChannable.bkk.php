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
class OrderpushChannable //implements CronJobInterface
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
       //->addFieldToFilter('entity_id', array("eq" => 555))
       ->addFieldToFilter('state', 'new')
       ->addFieldToFilter('order_ref_nbr', array("neq" => 'NULL'))
       ->addFieldToFilter('channable_id', array("neq" => 'NULL'))
       ->addFieldToFilter('channel_id', array("neq" => 'NULL'))
       ->addFieldToFilter('afas_order_nbr', array("null" => true))
       ->setOrder(
                'created_at',
                'desc'
            );
       //echo count($collection);
       return $collection;
    }


    private function getClient(){

       $this->logger->debug('OrderPushChannable: start');
        $client = $this->_restCurlClient->getClient();
        $OrderData = [];
        $this->logger->debug('OrderPush: start count'.count($this->getOrders()));
        foreach ($this->getOrders() as $order) {
            $orderInfo = $this->getOrderInfo($order, []);
           //Loop through each item and fetch data
            $orderItems = $this->getOrderItems($order, $products = [], $totalQty = 0);

            $orderGroupItem = $this->getProductType($order, $products = [], $orderInfo['countryId']);

            $orderProducts = $orderItems['products'];


            $totalQty = $orderItems['totalQty'];

            if($orderInfo['countryId'] =='BE'){
                  $data['Fields'] = array(
                                    "ItCd" => 'TR-DS-BE',
                                    "QuUn" => $totalQty,
                                    "VaIt" => 2,
                                    "BuUn" => "STK"
                                  );
                  $orderProducts['Element'][] = $data;
            }
            //$elements = json_encode($orderProducts);

            $OrderData['Fields'] = $orderInfo;

            if(isset($orderGroupItem['products']) && !empty($orderGroupItem['products'])){
              $OrderData['Objects'] = $orderGroupItem['products'];
            }
            else {
              $OrderData['Objects'] = $orderProducts;
            }
//print_r($OrderData);
            $afasOrder = [];
            foreach ($OrderData['Objects']['Element'] as $sourceKey => $items) {
               $OrderData['Objects']['Element'] = $items;
               $FbSales = $this->setFbSales($OrderData, [], $sourceKey);

               $this->logger->debug('OrderPush:'.$FbSales);

               $suc = $client->callAfas('POST', 'connectors/FbSales', [], $FbSales);

               $dataResult = json_decode($suc);
               //$this->logger->debug('OrderPush: result'.$suc);
               $afasOrder[] = $dataResult->results->FbSales->OrNu;

        }
            //$this->logger->debug('OrderPush: $order->getRealOrderId()'.$order->getRealOrderId());

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

 public function getOrderItems($order, $products, $totalQty){


            foreach ($order->getAllVisibleItems() as $item)
            {
              //if ($item->getProductType() != 'configurable') {

               $data['Fields'] = array(
                                    "ItCd" => $item->getSku(),
                                    "QuUn" => $item->getQtyOrdered(),
                                    "VaIt" => 2,
                                    "BuUn" => "STK"
                                  );
               $totalQty += $item->getQtyOrdered();

               $sourceItemList = $this->getSourceItemBySku($item->getSku());
               $sourceCode = 'default';
               $sourceList = [];
                  foreach ($sourceItemList as $source) {
                      $sourceList[$source->getSourceCode()] = $source->getQuantity();
                      /*if($source->getQuantity() > 0){
                        $sourceCode = $source->getSourceCode();
                      }*/
                  }
                if(isset($sourceList['brouwers_text']) && $sourceList['brouwers_text'] > 0){
                    $sourceCode = 'brouwers_text';
                }
                elseif(isset($sourceList['brouwers_indutex']) && $sourceList['brouwers_indutex'] > 0){
                    $sourceCode = 'brouwers_indutex';
                }
                elseif(isset($sourceList['pml_text']) && $sourceList['pml_text'] > 0){
                    $sourceCode = 'pml_text';
                }
                elseif(isset($sourceList['tilburg_text']) && $sourceList['tilburg_text'] > 0){
                    $sourceCode = 'tilburg_text';
                }
                elseif(isset($sourceList['warcoing_text']) && $sourceList['warcoing_text'] > 0){
                    $sourceCode = 'warcoing_text';
                }
                else{
                    $sourceCode = 'default';
                }

               $products['Element'][$sourceCode][] = $data;

            //}
          }
            return array('products' => $products, 'totalQty' => $totalQty);

 }

 public function getProductType($order, $products, $countryId, $productOptions=[]){

  foreach ($order->getAllItems() as $orderItem) {
     $options = $orderItem->getProductOptions();
        if(!empty($options['info_buyRequest'])) {
          if(!empty($options['info_buyRequest']['super_product_config'])){
          if(!empty($options['info_buyRequest']['super_product_config']['product_type']=='grouped')){
                $productOptions[] = $options['info_buyRequest']['super_product_config']['product_id'];
            }
          }
        }
    }
    $productId = array_unique($productOptions);
    if(count($productId)>0){
      $item = $this->getProductById($productId[0]);

            $data['Fields'] = array(
                                    "ItCd" => $item->getSku(),
                                    "QuUn" => 1,
                                    "VaIt" => 7,
                                    "BuUn"=> "set",
                                    "Upri"=> "235",
                                    "CoPr"=> "235"
                                  );
               $products['Element'][] = $data;

               if($countryId =='BE'){
                  $data['Fields'] = array(
                                    "ItCd" => 'TR-DS-BE',
                                    "QuUn" => 1,
                                    "VaIt" => 2,
                                    "BuUn" => "STK"
                                  );
                  $products['Element'][] = $data;
        }
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
                    'U69281111456C5BCE54EB75BAF6D5304F' => strtoupper($sourceKey[0]).$data['Fields']['orderReference'],
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
                    'U1B5E25A24A488D4462334A832C917F7D' => "I"

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


}
