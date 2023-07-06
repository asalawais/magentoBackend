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
class OrderpushHomeShopping //implements CronJobInterface
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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        OrderHelper $orderHelper,
        CurlFetch $curlHelper,
        \Magento\Sales\Model\Order $orderModel
    )
    {
        $this->_productRepository = $productRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->_orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->productRepositoryInterface = $productRepositoryInterface;
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

    private function getOrder($id)
    {
        $order = $this->_orderModel;
        return $order->loadByIncrementId($id);
    }

    public function getProductById($id)
    {
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
        if ($Artikelgroep = $product->getCustomAttribute('Artikelgroep')) {
            $aGroep = $Artikelgroep->getValue();
        }
        if ($PreferredWarehouse = $product->getCustomAttribute('preferred_warehouse')) {
            $pWarehouse = $PreferredWarehouse->getValue();
        }
        if ($aGroep == 8020 && $pWarehouse == 'PML Text Drops.') {
            $unit = 2;
        }
        return $unit;
    }

    public function getOrders()
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 'complete')
            ->addFieldToFilter('state', 'complete');
            //->addFieldToFilter('order_ref_nbr', ["neq" => 'NULL'])
            //->addFieldToFilter('afas_order_nbr', ["null" => true])
            //->addFieldToFilter('mollie_transaction_id', ["neq" => 'NULL']);
        $collection->getSelect()->limit(3);
        echo count($collection);
        die('lll');
        return $collection;
    }


    private function getClient()
    {

        $this->logger->debug('OrderPush HomeShopping: start');
        $client = $this->_restCurlClient->getClient();
        $OrderData = [];
        $this->logger->debug('OrderPush HomeShopping: start count' . count($this->getOrders()));
        foreach ($this->getOrders() as $order) {
            try{
            $orderInfo = $this->getOrderInfo($order, []);
            //Loop through each item and fetch data
            $orderItems = $this->getOrderItems($order, []);
            $orderProducts = $orderItems['products'];
            $OrderData['Fields'] = $orderInfo;
            $OrderData['Objects'] = $orderProducts;

            $afasOrder = [];
            foreach ($OrderData['Objects']['Element'] as $sourceKey => $items) {
                $OrderData['Objects']['Element'] = $items;
                $OrderData['Fields']['Unit'] = $orderProducts['unit'];
                $FbSales = $this->setFbSales($OrderData, [], $sourceKey);
                //echo $FbSales;
                $this->logger->critical('OrderPush HomeShopping:' . $FbSales);
                /*$suc = $client->callAfas('POST', 'connectors/FbSales', [], $FbSales);
                $dataResult = json_decode($suc);
                $this->logger->critical('OrderPush HomeShopping: result' . $suc);
                $afasOrder[] = $dataResult->results->FbSales->OrNu;*/

            }

            //for dummy
            $orderItemsDummy = $this->getOrderItemsDummy($order, []);
            $orderProductsDummy = $orderItemsDummy['products'];
            $OrderDataDummy['Fields'] = $orderInfo;
            $OrderDataDummy['Objects'] = $orderProductsDummy;

            foreach ($OrderDataDummy['Objects']['Element'] as $sourceKey => $itemsDummy) {
                $OrderDataDummy['Objects']['Element'] = $itemsDummy;
                $OrderDataDummy['Fields']['Unit'] = $orderProductsDummy['unit'];
                $FbSalesDummy = $this->setFbSalesForDummy($OrderDataDummy, [], $sourceKey);
                $this->logger->critical('OrderPush HomeShopping :' . $FbSalesDummy);
                /*$sucDummy = $client->callAfas('POST', 'connectors/FbSales', [], $FbSalesDummy);
                $dataResultDummy = json_decode($sucDummy);
                $this->logger->critical('OrderPush HomeShopping: result' . $dataResultDummy);
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
                    $this->logger->critical('OrderPush channable: GUID result' . print_r($guidResult, true));

                    $packingSlipResult = $this->curlHelper->getPackingSlip($dataResult->results->FbSales->OrNu, $OrderData, 10329);
                    $packingSlips[] = $packingSlipResult['results']['FbDeliveryNote']['OrNu'];
                    $this->logger->critical('OrderPush channable: result' . print_r($packingSlips, true));

                //$afasOrder[] = $dataResultDummy->results->FbSales->OrNu;*/

            }
            /*$orderN = $this->getOrder($order->getRealOrderId());
            //$this->logger->debug('OrderPush: $orderN->getId()'.$orderN->getId());
            if ($orderN->getId() && !empty($afasOrder)) {
                $orderN->setData('afas_order_nbr', implode(",", $afasOrder));
            $orderN->setData('afas_packing_slip_nbr', implode(",", $packingSlips));
                $orderN->setData('afas_order_status', "I");
                //$orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                //$orderN->setState($orderState)->setStatus($orderState);
                $orderN->save();

            // save sales grid data
                  $this->orderHelper->salesGridDataSave($orderN);
            }*/

            }
            catch (\Exception $e){
                $this->logger->critical('OrderPush HomeShopping: '.$e->getMessage());
            }
        }

    }


    public function getOrderInfo($order, $orderInfo)
    {

        $orderRef = $this->getOrder($order->getRealOrderId());
        $orderInfo['total'] = $orderRef->getData('order_cod'); //$order->getGrandTotal();
        $orderInfo['name'] = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
        $orderInfo['shipName'] = $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname();
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
        $orderInfo['RfCs'] = 'HomeShopping24.nl';
        $orderInfo['debtorId'] = $orderRef->getData('debtor_id');
        //$next10daysTimeZone = $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d H:i:s');
        //$orderInfo['next10days'] = date('Y-m-d', strtotime($next10daysTimeZone. '+10 weekdays'));


        return $orderInfo;

    }

    private function getOrderItems($order, $products)
    {


        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'configurable') {
                $sku = $item->getProductOptions()['simple_sku'];
            } else {
                $sku = $item->getSku();
            }

            $data['Fields'] = array(
                "ItCd" => $sku,
                "QuUn" => $item->getQtyOrdered(),
                "VaIt" => 2,
                "BuUn" => "STK"
            );
            $sourceCode = 'default';
            $sourceItemList = $this->getSourceItemBySku($item->getSku());
            $product = $this->loadProductBySku($sku);
            $unit = $this->getAfasUnit($product->getId());
            foreach ($sourceItemList as $source) {
                if ($source->getSourceCode() != 'default') {
                    $sourceCode = $source->getSourceCode();
                    break;
                }
            }
            //$sourceCode = 'default';
            $products['Element'][$sourceCode][] = $data;
            $products['unit'] = $unit;

        }
        return array('products' => $products);

    }

    private function getOrderItemsDummy($order, $products)
    {
        foreach ($order->getAllVisibleItems() as $item) {

            $data['Fields'] = array(
                "ItCd" => 60010277,
                "QuUn" => 1,
                "VaIt" => 2,
                "BuUn" => "STK"
            );

            $sourceCode = 'default';
            $products['Element'][$sourceCode][] = $data;
            $products['unit'] = 6;

        }
        return array('products' => $products);

    }

    private function setFbSales($data, $dataAfas, $sourceKey)
    {
        $PrId = '';
        $countryCode = $data['Fields']['countryId'];
        $country['BE'] = 'B';
        $country['DE'] = 'D';
        $country['NL'] = 'NL';
        if ($countryCode == 'DE' || $countryCode == 'BE') {
            $countryCode = $country[$countryCode];
        }
        $dataAfas['Element']['Fields'] = [

            'RfCs' => $data['Fields']['RfCs'] . ' / ' . $countryCode,
            'U69281111456C5BCE54EB75BAF6D5304F' => $data['Fields']['orderReference'],
            'OrDa' => $data['Fields']['dateTimeZone'],
            //'DaDe' => $data['Fields']['next10days'],
            'DbId' => 10329,
            'VaDu' => 6,
            'CuId' => 'EUR',
            'Unit' => $data['Fields']['Unit'],
            'War' => $this->getWarehouse($sourceKey),
            'PrId' => $PrId,
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

    private function setFbSalesForDummy($data, $dataAfas, $sourceKey)
    {
        $PrId = '';
        $countryCode = $data['Fields']['countryId'];
        $country['BE'] = 'B';
        $country['DE'] = 'D';
        $country['NL'] = 'NL';
        if ($countryCode == 'DE' || $countryCode == 'BE') {
            $countryCode = $country[$countryCode];
        }
        $dataAfas['Element']['Fields'] = [

            'RfCs' => $data['Fields']['RfCs'] . ' / ' . $countryCode,
            'U69281111456C5BCE54EB75BAF6D5304F' => $data['Fields']['orderReference'],
            'OrDa' => $data['Fields']['dateTimeZone'],
            //'DaDe' => $data['Fields']['next10days'],
            'DbId' => 10277,
            'VaDu' => 2,
            'CuId' => 'EUR',
            'Unit' => 6,
            'War' => $this->getWarehouse($sourceKey),
            'PrId' => $PrId,
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

    public function getWarehouse($war)
    {

        /*$warehouses = [
          'default'          => 'PML Text Drops.'
        ];*/
        $warehouses = [
            'default' => 'PML Text Drops.',
            'pml_text_drops' => 'PML Text Drops.',
            'pml' => 'PML'
        ];
        return $warehouses[$war];

    }


}
