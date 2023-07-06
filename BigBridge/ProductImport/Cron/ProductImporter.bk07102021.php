<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;
use BigBridge\ProductImport\Helper\CurlFetch;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductPublisher
 */
class ProductImporter //implements CronJobInterface
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
        SearchCriteriaBuilderFactory              $criteriaBuilderFactory,
        ProductRepositoryFactory                  $repositoryFactory,
        StoreManagerInterface                     $storeManager,
        \Psr\Log\LoggerInterface                  $logger,
        \Magento\Framework\Module\Dir\Reader      $moduleReader,
        \Magento\Framework\Filesystem             $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        //ConfigInterface $config,
        CurlFetch                                 $productsArray
    )
    {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->productsArray = $productsArray;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->logger = $logger;
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

    /**
     * @return void
     * @throws InputException
     * @throws StateException
     *
     * @throws CouldNotSaveException
     */
    private function createXMLProducts()
    {
        $rows = $this->productsArray->getProductRows();
        //print_r($rows);
        //die;
        $categories = $this->getCategories();
        //print_r($categories);

        $xml = '';
        $importXML = '';
        $articleGroup = [];
        if ($rows > 0) {
            foreach ($this->filteredArticles($rows) as $filteredArticle) {
                //echo $filteredArticle['productGroup'];
                $pgc = substr($filteredArticle['productGroup'], 2);
                if (isset($categories[$pgc])) {
                    //$cats = $categories[$pgc];
                    $root = 'Default Category/';
                    //$level = $cats['level1'];
                    //$level12 = implode('/', [$cats['level1'],$cats['level2']]);
                    //$level123 = implode('/', [$cats['level1'],$cats['level2'],$cats['level3']]);
                    //$level122 = implode('/', [$cats['level1'],$cats['level2-2']]);
                    //$level12232 = implode('/', [$cats['level1'],$cats['level2-2'],$cats['level3-2']]);
                    //$level12333 = implode('/', [$cats['level1'],$cats['level2-3'],$cats['level3-3']]);

                    $level1 = '';
                    $level2 = '';
                    $level3 = '';
                    $level4 = '';
                    $level5 = '';
                    $level6 = '';
                    $level7 = '';
                    $level8 = '';
                    if ($filteredArticle['Magento_cat._01'] != null) {
                        $level1 = $root . ucfirst(strtolower($filteredArticle['Magento_cat._01']));
                    }
                    if ($filteredArticle['Magento_cat._02'] != null) {
                        $level2 = $level1 . '/' . $filteredArticle['Magento_cat._02'];
                    }
                    if ($filteredArticle['Magento_cat._03'] != null) {
                        $level3 = $level1 . '/' . $filteredArticle['Magento_cat._03'];
                    }
                    if ($filteredArticle['Magento_cat._04'] != null) {
                        $level4 = $level1 . '/' . $filteredArticle['Magento_cat._04'];
                    }
                    if ($filteredArticle['Magento_cat._05'] != null) {
                        $level5 = $level1 . '/' . $filteredArticle['Magento_cat._05'];
                    }
                    if ($filteredArticle['Magento_cat._06'] != null) {
                        $level6 = $level1 . '/' . $filteredArticle['Magento_cat._06'];
                    }
                    if ($filteredArticle['Magento_cat._07'] != null) {
                        $level7 = $level1 . '/' . $filteredArticle['Magento_cat._07'];
                    }
                    if ($filteredArticle['Magento_cat._08'] != null) {
                        $level8 = $level1 . '/' . $filteredArticle['Magento_cat._08'];
                    }

                    //$productCategories = implode(',', [$root . $level,$root . $level12,$root . $level123,$root . $level122,$root . $level12232,$root . $level12333]);
                    $productCategories = implode(',', array_filter([
                            $level1,
                            $level2,
                            $level3,
                            $level4,
                            $level5,
                            $level6,
                            $level7,
                            $level8
                        ])
                    );
                    $filteredArticle['productCategories'] = $productCategories;
                    //$filteredArticle['productCategories'] = $root.'Default Category';
                } else {
                    $filteredArticle['productCategories'] = 'Default Category';
                }

                $agc = $filteredArticle['articlegroupCode'];
                $articleGroup[$agc][] = $filteredArticle;
            }

            //print_r($articleGroup);
            $generatedXml = $this->prepareConfigurableProducts($articleGroup);
            /*foreach ($rows as $row) {
        		if($row['b2c']==1){
        			$xml .= $this->generateSimpleXML($row);
        		}
        	}*/

            $importXML = '<?xml version="1.0" encoding="UTF-8"?>
							<import>' . $generatedXml . '</import>';
        }

        return $importXML;
    }

    private function generateSimpleXML($data, $categories, $attributeSet)
    {
        if ($data['qty_box'] == null) {
            $data['qty_box'] = 1;
        }
        if ($data['Bedrag'] == null) {
            $data['Bedrag'] = 0;
        }
        $xmlSimple = '<simple sku="' . $data['itemCode'] . '">
			<attribute_set_name>' . $attributeSet . '</attribute_set_name>
			<global>
				<name>' . str_replace('&', '&#38;', $data['description']) . '</name>
				<price>' . ((float)$data['Consumer_Price']) . '</price>
				<status>1</status>
				<visibility>1</visibility>
				<description>' . str_replace('&', '&#38;', $data['productTekst']) . '</description>
			    <short_description>' . str_replace('&', '&#38;', $data['description']) . '</short_description>
				<tax_class_name>Taxable Goods</tax_class_name>
                <select code="size">' . $data['size'] . '</select>
                <custom code="gtin">' . $data['gtin'] . '</custom>
                <select code="corner_height">' . $data['cornerHeight'] . '</select>
                <select code="closure">' . $data['Closure'] . '</select>
                <select code="quality">' . $data['quality'] . '</select>
                <multi_select code="product_brand"><item>' . $data['brand'] . '</item></multi_select>
                <color>' . $data['color'] . '</color>
                <weight>' . $data['weightGsm'] . '</weight>'
            . $this->callAttributeSet($attributeSet, $data)
            . $this->generateSimpleURLKey($data['description'], $data['itemCode']) .
            '</global>
			<website_codes>
				<item>base</item>
			</website_codes>'
            . $this->getCategoriesByGC($categories) .
            '<stock>
			<qty>0</qty>
			<is_in_stock>0</is_in_stock>
			</stock>
		</simple>';
        return $xmlSimple;
    }

    private function generateConfigurableXML($sku, $name, $simple_skus, $categories, $attributeSet)
    {
        $xmlConf = '<configurable sku="conf_' . $sku . '">
		<attribute_set_name>' . $attributeSet . '</attribute_set_name>
		<global>
			<name>' . str_replace('&', '&#38;', $name) . '</name>
			<price>0</price>
			<status>1</status>
			<visibility>4</visibility>
			<tax_class_name>Taxable Goods</tax_class_name>'
            . $this->generateURLKey($name, $sku) .
            '</global>
		<stock>
			<is_in_stock>1</is_in_stock>
		</stock>
		<website_codes>
			<item>base</item>
		</website_codes>'
            . $this->getCategoriesByGC($categories) .
            '<super_attribute_codes>
			<item>size</item>
		</super_attribute_codes>
		<variant_skus>
			' . $simple_skus . '
		</variant_skus>
	</configurable>';
        return $xmlConf;
    }

    private function prepareConfigurableProducts($articles)
    {
        $xml = '';

        foreach ($articles as $artGrpCode => $simpleArray) {
            $configName = $simpleArray[0]['Sitenaam'] . ' ' . $simpleArray[0]['color'];
            if (isset($simpleArray[0]['productCategories'])) {
                $categories = $simpleArray[0]['productCategories'];
            }
            $artikelCode = substr($simpleArray[0]['Artikelcode'], 0, 3);
            //$artikelCode = explode("-", $simpleArray[0]['Artikelcode']);
            //$np = explode(' ', $configName);
            //echo $configName = implode(" ", array_splice($np, 0, 3));
            //echo "<br>";
            $skus = '';

            foreach ($simpleArray as $key => $simple) {
                $xml .= $this->generateSimpleXML($simple, $categories, $artikelCode);
                $skus .= $this->prepareConfigurableSkus($simple['itemCode']);
            }
            $xml .= $this->generateConfigurableXML($artGrpCode, $configName, $skus, $categories, $artikelCode);
        }

        return $xml;
    }

    private function prepareConfigurableSkus($sku)
    {
        return '<item>' . $sku . '</item>';
    }

    public function writeXML($content)
    {
        //$dir = $this->directoryList->getPath('var');
        $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $fileName = $var . 'import/afas_products.xml';
        $myfile = fopen($fileName, "w") or die("Unable to open file!");
        try {
            fwrite($myfile, $content);
            fclose($myfile);
        } catch (Exception $e) {
        }
        return;
    }

    private function filteredArticles($rows)
    {
        $filteredArticles = [];
        foreach ($rows as $row) {
            $fa = $row['itemCode'];
            $filteredArticles[$fa] = $row;
        }
        return $filteredArticles;
    }

    private function getCategoriesByGC($categories)
    {
        $catsXML = '';
        if ($categories != '') {
            //$categories = "Boxsprings,Matrassen,Toppers";
            $cats = explode(',', $categories);

            $catsXML .= '<category_global_names>';
            //$root = 'Default Category/';
            foreach ($cats as $c) {
                //$c=$root.$c;
                $catsXML .= '<item>' . rtrim($c, "/") . '</item>';
            }
            $catsXML .= '</category_global_names>';
        }
        return $catsXML;
    }

    private function generateURLKey($name, $sku)
    {
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $postfix = str_replace("conf_", "-", $sku);
        $url = strtolower($url . '-' . $postfix);

        return '<url_key>' . $url . '</url_key>';
    }

    private function generateSimpleURLKey($name, $sku)
    {
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $postfix = $sku;
        $url = strtolower($url . '-' . $postfix);

        return '<url_key>' . $url . '</url_key>';
    }

    private function getCategories()
    {
        $media = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $file = fopen($media . 'import/mps_categories.csv', 'r');
        $all_rows = [];
        $header = fgetcsv($file);
        //print_r($header);
        while (($line = fgetcsv($file)) !== false) {
            $all_rows[$line[0]] = array_combine($header, $line);
        }
        fclose($file);

        return $all_rows;
    }

    public function getFIS($data): string
    {
        return '<custom code="kwaliteit_gsm_fis">' . $data['Kwaliteit__gsm___-_FIS'] . '</custom>
        <select code="geschikt_voor_droger_fis">' . $data['Geschikt_voor_droger_-_FIS'] . '</select>
        <custom code="wasvoorschrift_fis">' . $data['Wasvoorschrift_-_FIS'] . '</custom>
        <select code="waterdicht_fis">' . $data['Waterdicht_-_FIS'] . '</select>
        <select code="strijkvrij_fis">' . $data['Strijkvrij_-_FIS'] . '</select>
        <select code="elastieke_rand_rondom_fis">' . $data['Elastieke_rand_rondom_-_FIS'] . '</select>
        <custom code="matrashoogte_fis">' . $data['Matrashoogte_-_FIS'] . '</custom>
        <custom code="hoekhoogte_fis">' . $data['Hoekhoogte_-_FIS'] . '</custom>
        <custom code="materiaal_fis">' . $data['Materiaal_-_FIS'] . '</custom>';

    }

    public function getPIL($data): string
    {

        return '<custom code="wasvoorschrift_pil">' . $data['Wasvoorschrift_-_PIL'] . '</custom>
        <select code="geschikt_voor_droger_pil">' . $data['Geschikt_voor_droger_-_PIL'] . '</select>
        <custom code="ritssluiting_pil">' . $data['Ritssluiting_-_PIL'] . '</custom>
        <custom code="anti_allergisch_pil">' . $data['Anti-allergisch_-_PIL'] . '</custom>
        <custom code="vulgewicht_pil">' . $data['Vulgewicht_-_PIL'] . '</custom>
        <custom code="vulling_pil">' . $data['Vulling_-_PIL'] . '</custom>
        <custom code="tijk_pil">' . $data['Tijk_-_PIL'] . '</custom>';

    }

    public function getQCS($data): string
    {

        return '<custom code="geschikt_voor_droger_qcs">' . $data['Geschikt_voor_droger_-_QCS'] . '</custom>
        <custom code="wasvoorschrift_qcs">' . $data['Wasvoorschrift_-_QCS'] . '</custom>
        <custom code="stijl_qcs">' . $data['Stijl_-_QCS'] . '</custom>
        <custom code="kussensloop_qcs">' . $data['Kussensloop_-_QCS'] . '</custom>
        <select code="instopstrook_qcs">' . $data['Instopstrook_-_QCS'] . '</select>
        <custom code="kwaliteit_qcs">' . $data['Kwaliteit_-_QCS'] . '</custom>
        <custom code="materiaal_qcs">' . $data['Materiaal_-_QCS'] . '</custom>';
    }

    public function getQUI($data): string
    {

        return '<custom code="vulgewicht_qui">' . $data['Vulgewicht_-_QUI'] . '</custom>
        <custom code="tijk_qui">' . $data['Tijk_-_QUI'] . '</custom>
        <select code="geschikt_voor_droger_qui">' . $data['Geschikt_voor_droger_-_QUI'] . '</select>
        <custom code="wasvoorschrift_qui">' . $data['Wasvoorschrift_-_QUI'] . '</custom>
        <custom code="eigenschappen_qui">' . $data['Eigenschappen_-_QUI'] . '</custom>
        <custom code="vulgewicht_samengevoegd_qui">' . $data['Vulgewicht_samengevoegd_-_QUI'] . '</custom>
        <custom code="vulgewicht_herfstdeel_qui">' . $data['Vulgewicht_herfstdeel_-_QUI'] . '</custom>
        <custom code="vulgewicht_zomerdeel_qui">' . $data['Vulgewicht_zomerdeel_-_QUI'] . '</custom>
        <custom code="vulling_qui">' . $data['Vulling_-_QUI'] . '</custom>';
    }

    public function callAttributeSet($attributeSet, $data)
    {
        switch ($attributeSet) {
            case 'FIS':
                return $this->getFIS($data);
            case 'PIL':
                return $this->getPIL($data);
            case 'QCS':
                return $this->getQCS($data);
            case 'QUI':
                return $this->getQUI($data);
        }
    }

    public function combineCategories($filteredArticle){
        $root = 'Default Category/';
        //$level = $cats['level1'];
        //$level12 = implode('/', [$cats['level1'],$cats['level2']]);
        //$level123 = implode('/', [$cats['level1'],$cats['level2'],$cats['level3']]);
        //$level122 = implode('/', [$cats['level1'],$cats['level2-2']]);
        //$level12232 = implode('/', [$cats['level1'],$cats['level2-2'],$cats['level3-2']]);
        //$level12333 = implode('/', [$cats['level1'],$cats['level2-3'],$cats['level3-3']]);

        $level1 = '';
        $level2 = '';
        $level3 = '';
        $level4 = '';
        $level5 = '';
        $level6 = '';
        $level7 = '';
        $level8 = '';
        if ($filteredArticle['Magento_cat._01'] != null) {
            $level1 = $root . ucfirst(strtolower($filteredArticle['Magento_cat._01']));
        }
        if ($filteredArticle['Magento_cat._02'] != null) {
            $level2 = $level1 . '/' . $filteredArticle['Magento_cat._02'];
        }
        if ($filteredArticle['Magento_cat._03'] != null) {
            $level3 = $level1 . '/' . $filteredArticle['Magento_cat._03'];
        }
        if ($filteredArticle['Magento_cat._04'] != null) {
            $level4 = $level1 . '/' . $filteredArticle['Magento_cat._04'];
        }
        if ($filteredArticle['Magento_cat._05'] != null) {
            $level5 = $level1 . '/' . $filteredArticle['Magento_cat._05'];
        }
        if ($filteredArticle['Magento_cat._06'] != null) {
            $level6 = $level1 . '/' . $filteredArticle['Magento_cat._06'];
        }
        if ($filteredArticle['Magento_cat._07'] != null) {
            $level7 = $level1 . '/' . $filteredArticle['Magento_cat._07'];
        }
        if ($filteredArticle['Magento_cat._08'] != null) {
            $level8 = $level1 . '/' . $filteredArticle['Magento_cat._08'];
        }

        //$productCategories = implode(',', [$root . $level,$root . $level12,$root . $level123,$root . $level122,$root . $level12232,$root . $level12333]);
        $productCategories = implode(',', array_filter([
                $level1,
                $level2,
                $level3,
                $level4,
                $level5,
                $level6,
                $level7,
                $level8
            ])
        );

        return $productCategories;
    }
}
