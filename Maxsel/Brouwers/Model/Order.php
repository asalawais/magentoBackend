<?php

namespace Maxsel\Brouwers\Model;

use Maxsel\Brouwers\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PracticalAfas\Client\RestCurlClient;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Order
{

    private $afasConnect;
    protected $timezone;
    protected $_dir;
    protected $shipment;
    protected $orderCollectionFactory;
    protected $_shipmentCollection;

    public function __construct(
        Data $afasConnect,
        \Magento\Framework\Filesystem\DirectoryList $dirList,
        \Maxsel\Brouwers\Model\Shipment $shipment,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollection,
        CollectionFactory $orderCollectionFactory,
        TimezoneInterface $timezone
    )
    {
        $this->afasConnect = $afasConnect;
        $this->_dir = $dirList;
        $this->shipment = $shipment;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->timezone = $timezone;
        $this->_shipmentCollection = $shipmentCollection;
    }

    public function call()
    {
        $sourcePath = $this->_dir->getPath('var') . '/import/brouwers/';
        $destPath = $this->_dir->getPath('var') . '/import/brouwers-processed/';
        foreach (glob($this->_dir->getPath('var') . '/import/brouwers/*.*') as $file) {
            if (is_file($file)) {
                //echo "filename: ." . $file . "<br />";
                $xml = file_get_contents($file);
                try {
                    $result = $this->process($xml);
                    if($result){
                        echo $sourcePath.basename($file);
                        echo $destPath.basename($file);
                        copy($sourcePath.basename($file), $destPath.basename($file));
                        unlink($sourcePath.basename($file));
                    }
                } catch (\Exception $e) {


                }
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process($data)
    {
        try {
            $brouwers = [];
            $xml = new \SimpleXMLElement($data);
            $DPU = $xml->xpath('//DPU')[0];
            $HEADER = $xml->xpath('//HEADER')[0];
            //$afasOrderId = $HEADER->{'REFERENCE-1'};
            $brouwers['afasOrderId'] = trim($HEADER->{'REFERENCE-1'});
            $brouwers['traceTrace'] = trim($DPU->{'TRACK-TRACE'});
            //echo 'REFERENCE-1: ' . $afasOrderId . '<br>';
            echo 'TRACK-TRACE-before: ' . $DPU->{'TRACK-TRACE'} . '<br>';
            //echo $DPU->{'TRACK-TRACE'} . '<br>';


            /**
             * @var \Magento\Sales\Model\Order $order
             */
            if (!is_null($brouwers['traceTrace']) && !empty($brouwers['traceTrace'])) {
                //echo 'REFERENCE-1: ' . $afasOrderId . '<br>';
                echo 'TRACK-TRACE-after: ' . $DPU->{'TRACK-TRACE'} . '<br>';
                $trackingData = array(
                    'carrier_code' => 'dpd_dpd',
                    'title' => 'DPD',
                    'number' => $brouwers['traceTrace'], // Replace with your tracking number
                );
                $order = $this->getOrderbyAfasOrder($brouwers['afasOrderId']);
                if ($order) {
                    if($order->hasShipments()){
                        $shipments = $this->_shipmentCollection->create()
                            ->addFieldToFilter('order_id', $order->getId())
                            ->load();
                        foreach ($shipments as $shipment) {
                            $this->shipment->addTrackingToExistingShipment($shipment, $trackingData, $order);
                        }
                    }
                    else {
                        echo $order->getIncrementId();
                        echo 'Magento Order ID: ' . $order->getId() . '<br>';
                        $this->shipment->createShipment($order->getIncrementId(), $trackingData);
                        echo 'Back from shipment: ' . $order->getId() . '<br>';
                        //$this->updateAfasOrder($order->getIncrementId(), $DPU);
                    }
                    return true;
                }
            }
        }
        catch (\Exception $e){
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }



    }

    public function getOrderbyAfasOrder($id)
    {
        echo $id;
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('afas_order_nbr', trim($id));
        echo 'collection '.count($collection) . '<br>';
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
    }

    public function updateAfasOrder($OrNu, $DPU)
    {

        $client = new RestCurlClient(['customerId' => 89626, 'appToken' => '3E89D236B4414A3A9C2DD07C5FAC7911ADE9DDB740E1682BAE4FE2A57A9158DF']);
        echo '{
            "FbDeliveryNote": {
                "Element": [
                    {
                        "Fields": {
                            "OrNu": "' . $OrNu . '",
                            "U1894E7EC43E51449CDEF17ABAA571DD5": "' . $DPU->{'TRACK-TRACE'} . '"
                        }
                      }
                     ]
                    }
                }';
        $suc = $client->callAfas('PUT', 'connectors/FbDeliveryNote', [], '{
            "FbDeliveryNote": {
                "Element": [
                    {
                        "Fields": {
                            "OrNu": "' . $OrNu . '",
                            "U1894E7EC43E51449CDEF17ABAA571DD5": "' . $DPU->{'TRACK-TRACE'} . '"
                        }
                      }
                     ]
                    }
                }');
        return json_decode($suc);
    }


}
