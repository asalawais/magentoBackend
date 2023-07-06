<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;
use http\Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use BigBridge\ProductImport\Helper\CurlFetch;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use BigBridge\ProductImport\Cron\GalleryImporterXml;


/**
 * Class ProductPublisher
 */
class GalleryImporter //implements CronJobInterface
{
    const IMAGES_PATH = '/home/homeshopping24/domains/homeshopping24.nl/application/pub/media/afas/import/';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var ProductRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var ProductsArray
     */
    private $productsArray;

    /**
     * @var ConfigInterface
     */
    private $config;

    private $directoryList;

    protected $_filesystem;

    protected $_productCollectionFactory;

    protected $mediaDirectory;

    protected $galleryReadHandler;
    protected $imageProcessor;
    protected $productGallery;
    protected $galleryImporterXml;
    protected $attributeSetRepository;
    protected $_productRepository;
    protected $productVisibility;
    protected $productStatus;

    /**
     * ProductPublisher constructor.
     *
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ProductRepositoryFactory $repositoryFactory
     * @param ProductsArray $productsArray
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */
    public function __construct(
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        ProductRepositoryFactory $repositoryFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        ReadHandler $galleryReadHandler,
        Processor  $imageProcessor,
        Gallery $productGallery,
        GalleryImporterXml $galleryImporterXml,
        AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory

    ) {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->directoryList = $directoryList;
        $this->_filesystem = $filesystem;
        $this->mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        $this->galleryImporterXml = $galleryImporterXml;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->_productRepository = $productRepository;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        //$this->config = $config;
    }

    /**
     * Publish products
     *
     * @return bool
     */
    public function execute()
    {
        //$this->resetNames();
        $this->createXMLProducts();
        //$this->galleryImporterXml->execute();
        return true;
        //print_r($productXML);
        //if($productXML!=''){
        //	$this->writeXML($productXML);
        //}

    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     *
     * @return void
     */
    private function createXMLProducts()
    {
        $content = '';
        try {
        //$var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $var = '/home/homeshopping24/domains/homeshopping24.nl/application/var/';
        $productCollection = $this->getProductCollection();
        $productGroupedCollection = $this->getGroupedProductCollection();
        //$img = $this->mediaDirectory->getAbsolutePath() . 'afas'.DS.'import' . DS;
        $img = '/home/homeshopping24/domains/homeshopping24.nl/application/pub/media/afas/import/';

        foreach ($productCollection as $product){
                //$this->resetImageGallery($product);
            $content .= $this->isFileExist($product->getSku(), str_replace('&','&amp;', $product->getName()), 'simple');
        }


        foreach ($productGroupedCollection as $conf){
            //$this->resetImageGallery($conf);
            if (strpos($conf->getSku(), 'conf_') !== false) {
                $content .= $this->isFileExist(substr($conf->getSku(), 5), str_replace('&','&amp;', $conf->getName()), 'configurable');
            }
        }

        $varFile = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $fileName = $varFile. 'import'.DS.'afas_images'.'.xml';
        $myfile = fopen($fileName, "w") or die("Unable to open file!");
        $importXML = '<?xml version="1.0" encoding="UTF-8"?>
                       <import>'.$content.'</import>';
        fwrite($myfile, $importXML);
        fclose($myfile);
        } catch (Exception $e) {

        }
    }

    private function isFileExist($sku, $name, $type){
        $extensions = ['jpg', 'png', 'jpeg'];
        $suffixes = ['n'=>null, 'a'=>'_1', 'b'=>'_01', 'c'=>'-1'];

        foreach ($extensions as $ext) {
            /*$mainImage = self::IMAGES_PATH.$sku.'.'.$ext;
            if (file_exists($mainImage)) {
                if ($type == 'simple') {
                    return $this->setSimpleImages($sku, $mainImage, $name, $key, $ext);
                }
                else{
                    return $this->setConfigImages($sku, $mainImage, $name, $key, $ext);
                }
            }*/

            foreach ($suffixes as $key => $suffix){
                $mainImage = self::IMAGES_PATH.$sku.$suffix.'.'.$ext;
                if (file_exists($mainImage)) {
                    if ($type == 'simple') {
                        return $this->setSimpleImages($sku, $mainImage, $name, $key, $ext);
                    }
                    else{
                        return $this->setConfigImages($sku, $mainImage, $name, $key, $ext);
                    }
                }
            }
        }
    }

    private function setSimpleImages($sku, $img, $label, $suffix, $ext){
        $xml = '<simple sku="'.$sku.'">
             <attribute_set_name>'.$this->getAttributeSetName($sku).'</attribute_set_name>
              <images>
                <image file_or_url="'.$img.'">
                    <global>
                        <role>image</role>
                        <role>small_image</role>
                        <role>thumbnail</role>
                        <gallery_information label="'.$label.'" position="1" enabled="1" />
                    </global>
                </image>'
            . $this->getChildImages($sku, $label, $img, $suffix, $ext)
            .
            '</images>
        </simple>';
        return $xml;
    }
    private function setConfigImages($sku, $img, $label, $suffix, $ext){
        $xml = '<configurable sku="conf_'.$sku.'">
             <attribute_set_name>'.$this->getAttributeSetName('conf_'.$sku).'</attribute_set_name>
              <images>
                <image file_or_url="'.$img.'">
                    <global>
                        <role>image</role>
                        <role>small_image</role>
                        <role>thumbnail</role>
                        <gallery_information label="'.$label.'" position="1" enabled="1" />
                    </global>
                </image>'
            . $this->getChildImages($sku, $label, $img, $suffix, $ext)
            .
            '</images>
        </configurable>';
        return $xml;
    }

    private function getChildImages($sku, $label, $img, $suffix, $ext)
    {
        $xml = '';
        //$mediaPath = $this->mediaDirectory->getAbsolutePath() . 'afas'. DS .'import' . DS;
        $mediaPath = '/home/homeshopping24/domains/homeshopping24.nl/application/pub/media/afas/import/';
        $image = $mediaPath . $sku;
        for ($i = 1; $i <= 9; $i++) {
            if ($suffix == 'a') {
                if (file_exists($image . '_' . $i . '.' . $ext) && ($image . '_' . $i . '.' . $ext != $img)) {
                    $xml .= '<image file_or_url="' . $image . '_' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                }
            } elseif ($suffix == 'b') {
                if (file_exists($image . '_0' . $i . '.' . $ext) && ($image . '_0' . $i . '.' . $ext != $img)) {

                    $xml .= '<image file_or_url="' . $image . '_0' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                }
            } elseif ($suffix == 'c') {
                if (file_exists($image . '-' . $i . '.' . $ext) && ($image . '-' . $i . '.' . $ext != $img)) {

                    $xml .= '<image file_or_url="' . $image . '-' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                }
            } elseif ($suffix == 'n') {
                if (file_exists($image . '_' . $i . '.' . $ext) && ($image . '_' . $i . '.' . $ext != $img)) {
                    $xml .= '<image file_or_url="' . $image . '_' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                } elseif (file_exists($image . '_0' . $i . '.' . $ext) && ($image . '_0' . $i . '.' . $ext != $img)) {

                    $xml .= '<image file_or_url="' . $image . '_0' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                } elseif (file_exists($image . '-' . $i . '.' . $ext) && ($image . '-' . $i . '.' . $ext != $img)) {

                    $xml .= '<image file_or_url="' . $image . '-' . $i . '.' . $ext . '">
                <global>
                    <gallery_information label="' . $label . '" position="1" enabled="1" />
                </global>
            </image>';
                }

            }
        }
        return $xml;
    }
    public function getProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->setFlag('has_stock_status_filter', false);
        //$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        //$collection->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', 'qty=0');
         echo 'simple'.count($collection);
        //$collection->setPageSize(3);
        return $collection;
    }
    public function getGroupedProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        $collection->setFlag('has_stock_status_filter', false);

        echo 'config'.count($collection);
        return $collection;
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }
    public function getAttributeSetName($sku)
    {
        $product = $this->getProductBySku($sku);
        $attributeSet = $this->attributeSetRepository->get($product->getAttributeSetId());
        return $attributeSet->getAttributeSetName();
    }

    public function resetImageGallery($product)
    {
        try {
            $repository = $this->repositoryFactory->create();
            $product = $repository->get($product->getSku());
            if ($product) {
                $this->galleryReadHandler->execute($product);

                // Unset existing images
                $images = $product->getMediaGalleryImages();
                foreach ($images as $child) {
                    $this->productGallery->deleteGallery($child->getValueId());
                    $this->imageProcessor->removeImage($product, $child->getFile());
                }

            }
        } catch (\Exception $e) {

        }
    }
        private function resetNames(){
        $path = self::IMAGES_PATH;

        $di = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach($di as $name => $fio) {
            $newname = $fio->getPath() . DIRECTORY_SEPARATOR . strtolower( $fio->getFilename() );
            echo $newname, "\r\n";
            //rename($name, $newname); - first check the output, then remove the comment...
        }
    }


}
