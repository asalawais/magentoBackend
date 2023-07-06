<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;
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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use BigBridge\ProductImport\Helper\RestCurlClientConfig;

/**
 * Class ProductPublisher
 */
class StockImporter //implements CronJobInterface
{
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
    protected $logger;
    protected $_filesystem;
    protected $_file;
    private $_restCurlClient;
    protected $_productCollectionFactory;
    private $productRepository;

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
        \Magento\Catalog\Model\ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        RestCurlClientConfig $restCurlClient,
        CollectionFactory $productCollectionFactory,
        //ConfigInterface $config,
        CurlFetch $productsArray

    )
    {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->productRepository = $productRepository;
        $this->productsArray = $productsArray;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->logger = $logger;
        $this->_restCurlClient = $restCurlClient;
        $this->_productCollectionFactory = $productCollectionFactory;
        //$this->config = $config;
    }

    /**
     * Publish products
     *
     * @return void
     */
    public function execute()
    {
        $productXML = $this->createXMLProducts();
        //print_r($productXML);
        if ($productXML != '') {
            $this->writeXML($productXML);
        }

    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->addAttributeToSelect('sku');
        //$collection->setPageSize(3); // fetching only 3 products
        return $collection;
    }

    /**
     * @return void
     * @throws InputException
     * @throws StateException
     *
     * @throws CouldNotSaveException
     */
    private function createXMLProducts()
    {
        $client = $this->_restCurlClient->getClient();
        //local
        //$stockSources = ['default'=>'PML Text Drops.'];
        $stockSources = ['brouwers_text' => 'Brouwers Text ', 'ommen' => 'Ommen'];
        // $stockSources = ['brouwers_indutex'=>'Brouwers Indutex', 'brouwers_text'=>'Brouwers Text', 'ommen'=>'Ommen', 'pml_text'=>'Perfect Meubel Logistics Text', 'tilburg_text'=>'Tilburg Text', 'warcoing_text'=>'Warcoing Text'];
        $xml = '';
        $importXML = '';
        $articleGroup = array();
        $generatedXml = '';
        $afasSkus = [];
        foreach ($stockSources as $code => $stockSource) {

            $rows = $this->getStockRows($client, $stockSource);
            //print_r($rows);
            if ($rows > 0) {
                foreach ($rows as $filteredArticle) {
                    $afasSkus[] = $filteredArticle['Itemcode'];
                    $generatedXml .= $this->prepareStockProducts($filteredArticle, $code, trim($stockSource));
                }

            }
        }
        $zeroStockSkus = $this->prepareDiffArray($afasSkus);
        //print_r($zeroStockSkus);
        foreach ($zeroStockSkus as $sku) {
            $generatedXml .= $this->generateSimpleOutOfStockXML($sku);
        }
        $importXML = '<?xml version="1.0" encoding="UTF-8"?>
                            <import>' . $generatedXml . '</import>';
        return $importXML;


    }

    private function generateSimpleXML($data, $msiCode)
    {
        if ($data['Op_voorraad'] == 0 || $data['Op_voorraad'] == null) {
            $stock = 0;
            $status = 0;
        } else {
            $stock = 1;
            $status = 1;
        }
        $xmlSimple = '<simple sku="' . $data['Itemcode'] . '">
			<attribute_set_name>Default</attribute_set_name>
			<website_codes>
				<item>base</item>
			</website_codes>
			<store_view code="default">
			<status>'.$status.'</status>
		    </store_view>
			<global>
			<status>'.$status.'</status>
		    </global>
			<stock>
				<qty>' . $data['Op_voorraad'] . '</qty>
				<is_in_stock>' . $stock . '</is_in_stock>
			</stock>
            <source_item code="' . $msiCode . '">
                <quantity>' . ((int)$data['Op_voorraad']) . '</quantity>
                <status>'.$status.'</status>
            </source_item>
		</simple>';

        return $xmlSimple;
    }

    private function generateSimpleOutOfStockXML($sku)
    {
        $xmlSimple = '<simple sku="' . $sku . '">
			<attribute_set_name>Default</attribute_set_name>
			<global>
			<status>0</status>
		    </global>
			<website_codes>
				<item>base</item>
			</website_codes>
			<store_view code="default">
			<status>0</status>
		    </store_view>
			<stock>
				<qty>0</qty>
				<is_in_stock>0</is_in_stock>
			</stock>
            <source_item code="default">
                <quantity>0</quantity>
                <status>0</status>
            </source_item>
            <source_item code="brouwers_text">
                <quantity>0</quantity>
                <status>0</status>
            </source_item>
		</simple>';

        return $xmlSimple;
    }

    private function prepareStockProducts($articles, $code, $stockSource)
    {
        if ($this->getPreferredWareHouse($articles['Itemcode']) == $stockSource) {
            return $this->generateSimpleXML($articles, $code);
        }
        else {
            return $this->generateSimpleOutOfStockXML($articles['Itemcode']);
        }
    }

    public function getPreferredWareHouse($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            if ($PreferredWarehouse = $product->getCustomAttribute('preferred_warehouse')) {
                return $PreferredWarehouse->getValue();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    protected function prepareDiffArray($afasSkus)
    {
        $products = $this->getProductCollection();
        $localSkus = [];
        foreach ($products as $product) {
            $localSkus[] = $product->getSku();
        }
        $result = array_diff($localSkus, $afasSkus);
        return $result;
    }


    public function writeXML($content)
    {
        //$dir = $this->directoryList->getPath('var');
        $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $fileName = $var . 'import/afas_stock_products.xml';
        $myfile = fopen($fileName, "w") or die("Unable to open file!");
        try {
            fwrite($myfile, $content);
            fclose($myfile);
        } catch (Exception $e) {

        }
        return;
    }


    public function getStockRows($client, $stockSource)
    {

        $this->logger->debug('Stock Importer: start');

        $get = $client->callAfas(
            'GET',
            'connectors/Voorraad__virtueel__per_magazijn',
            [
                'take' => 2000,
                'filterfieldids' => 'Magazijn',
                'filtervalues' => $stockSource,
                'operatortypes' => '1'
            ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

}
