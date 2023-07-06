<?php
namespace Maxsel\ImportExportWP\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Automattic\WooCommerce\Client;

/**
 * @author Patrick van Bergen
 */
class Connect
{

    protected $logger;
    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    private $productAttributeMediaGallery;

    /**
     * @var Client
     */
    private $clientWP;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $storeManager;
    protected $_stockItemRepository;
    protected $_productRepository;
    protected $stockRegistry;
    protected $stockState;

    public function __construct(
       LoggerInterface $logger,
       ProductAttributeMediaGalleryManagementInterface $productAttributeMediaGallery,
       StoreManagerInterface $storeManager,
       CollectionFactory $collectionFactory,
       StockItemRepository $stockItemRepository,
       ProductRepositoryInterface $productRepository,
       StockRegistryInterface $stockRegistry,
       StockStateInterface  $stockState
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->productAttributeMediaGallery = $productAttributeMediaGallery;
        $this->storeManager = $storeManager;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->stockState =  $stockState;
    }


    private function clientWP(){
        return new Client(
            'http://boxspringgektenieuw.sampreview.nl',
            'ck_a358d6783be703f4845b48cb6c0e0ea20abdac75',
            'cs_1a618c09a004caffc8ee1f1904cf9d522a7f67b4',
            [
                'wp_api'  => true,
                'version' => 'wc/v3',
            ]
        );
    }
//http://wp-api.local
//local: ck_9c4be04b563fe08ac7de6643ee25dd18e04bc8c1
//local: cs_a5937d320a69a68da8a3efd090b4175ebaee78b7
//live: ck_a358d6783be703f4845b48cb6c0e0ea20abdac75
//live: cs_1a618c09a004caffc8ee1f1904cf9d522a7f67b4

    public function getProduct($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getProductsByIds($productIds): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('entity_id', array('in' => $productIds));
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        return $collection;
    }

    public function getProductsById($productId): \Magento\Framework\DataObject
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('entity_id', array('in' => $productId));
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        return $collection->getFirstItem();
    }

    /**
     * @param string $sku
     * @return ProductAttributeMediaGalleryEntryInterface[]
     */
    public function getMediaGallery($sku)
    {
        $gallery = [];
        try {
            $gallery = $this->productAttributeMediaGallery->getList($sku);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $gallery;
    }

    public function getImageUrl($file){
        $mediaUrl = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl.'catalog/product'.$file;
    }

    /**
     * @return bool
     * @throws InputException
     * @throws StateException
     *
     * @throws CouldNotSaveException
     */
    public function createProducts($productIds)
    {
        $productCollection = $this->getProductsByIds($productIds);
        $data = [];
        $images = [];
        foreach($productCollection as $product) {
            $images[] = ['src' => $this->getImageUrl($product->getData('image'))];
            //$images[] = ['src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'];

            $gallery = $this->getMediaGallery($product->getSku());
            if ($gallery) {
                foreach ($gallery as $image) {
                    $images[] = ['src' => $this->getImageUrl($image->getFile())];
                    //$images[] = ['src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'];
                }
            }
            $data[] = [
                'name' => $product->getName(),
                'type' => 'simple',
                'regular_price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'short_description' => $product->getShortDescription(),
                'categories' => [
                    [
                        'id' => 9
                    ],
                    [
                        'id' => 14
                    ]
                ],
                'images' => $images
            ];
        }
        $products = ['create' => $data];
        try {
            $result = $this->clientWP()->post('products/batch', $products);
            $this->logger->debug(print_r($result, true));
            $this->logger->debug(print_r($data, true));
            return true;

        } catch (\Exception $e) {
            $this->logger->debug($e);
        }

        //return true;

    }

    public function createProductById($productId)
    {
        $product = $this->getProductsById($productId);
        $images = [];
            $images[] = ['src' => $this->getImageUrl($product->getData('image'))];
            //$images[] = ['src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'];

            $gallery = $this->getMediaGallery($product->getSku());
            if ($gallery) {
                foreach ($gallery as $image) {
                    $images[] = ['src' => $this->getImageUrl($image->getFile())];
                    //$images[] = ['src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'];
                }
            }
            $data = [
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'type' => 'simple',
                'regular_price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'short_description' => $product->getShortDescription(),
                'categories' => [
                    [
                        'id' => 9
                    ],
                    [
                        'id' => 14
                    ]
                ],
                'images' => $images
            ];
        try {
            $result = $this->clientWP()->post('products', $data);
            $this->logger->debug(print_r($result, true));
            $this->logger->debug(print_r($data, true));
            return true;

        } catch (\Exception $e) {
            $this->logger->debug($e);
        }

        //return true;

    }

    public function getOrders(){
        return $this->clientWP()->get('orders', ['status' => 'processing']);
    }

}
