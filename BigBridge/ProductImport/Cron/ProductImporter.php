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
     * @return bool
     */
    public function execute()
    {
        $productXML = $this->createXMLProducts();
        //print_r($productXML);
        if ($productXML != '') {
            $this->writeXML($productXML);
            return true;
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
        //$categories = $this->getCategories();
        //print_r($categories);

        $xml = '';
        $importXML = '';
        $articleGroup = [];
        if ($rows > 0) {
            foreach ($this->filteredArticles($rows) as $filteredArticle) {
                //echo $filteredArticle['productGroup'];
                //$pgc = substr($filteredArticle['productGroup'], 2);
                //if (isset($filteredArticle['productGroup'])) {
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
                $filteredArticle['productCategories'] = $this->combineCategories($filteredArticle);
                //$filteredArticle['productCategories'] = $root.'Default Category';
                /*} else {
                    //$filteredArticle['productCategories'] = 'Default Category';
                }*/
                $agc = $filteredArticle['articlegroupCode'];
                $articleGroup[$agc][] = $filteredArticle;
            }

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

    private function generateSimpleXML($data, $attributeSet)
    {
        $categories = $this->combineCategories($data);
        if ($data['qty_box'] == null) {
            $data['qty_box'] = 1;
        }
        if ($data['Bedrag'] == null) {
            $data['Bedrag'] = 0;
        }
        if($data['configurableProduct']==null){
            $visibility = 4;
        }
        else{
            $visibility = 1;
        }

        $xmlSimple = '<simple sku="' . $data['itemCode'] . '">
			<attribute_set_name>' . $attributeSet . '</attribute_set_name>
			<global>
				<name>' . str_replace('&', '&#38;', $data['B2C_-_Title_2']) . '</name>
				<price>' . ((float)$data['Consumer_Price']) . '</price>
				<status>1</status>
				<visibility>'.$visibility.'</visibility>
				<description>' . str_replace('&', '&#38;', htmlspecialchars($data['productTekst'])) . '</description>
			    <short_description>' . str_replace('&', '&#38;', htmlspecialchars($data['productTekst'])) . '</short_description>
				<tax_class_name>Taxable Goods</tax_class_name>
                <select code="size">' . $data['size'] . '</select>
                <custom code="gtin">' . $data['gtin'] . '</custom>
                <select code="corner_height">' . $data['cornerHeight'] . '</select>
                <select code="closure">' . $data['Closure'] . '</select>
                <select code="quality">' . $data['quality'] . '</select>
                <multi_select code="product_brand"><item>' . $data['brand'] . '</item></multi_select>
                <color>' . $data['color'] . '</color>
                <weight>' . $data['weightGsm'] . '</weight>
                <custom code="kleur">' . $data['Kleur'] . '</custom>
                <custom code="old_price">' . $data['Old_price'] . '</custom>
                <custom code="itemtype_naam">' . $data['Itemtype_Naam'] . '</custom>
                <custom code="merknaam">' . $data['Merknaam'] . '</custom>
                <custom code="articlegroupCode">'.$data['articlegroupCode'].'</custom>
                <custom code="Artikelgroep">'.$data['Artikelgroep'].'</custom>
                <custom code="articlegroupSize">'.$data['articlegroupSize'].'</custom>
                <custom code="b2c_title">' . str_replace('&', '&#38;', $data['B2C_-_Title']) . '</custom>
                <custom code="B2C_Title_2">' . str_replace('&', '&#38;', $data['B2C_-_Title_2']) . '</custom>
                <custom code="Aantal_op_voorraad">'.$data['Aantal_op_voorraad'].'</custom>
                <custom code="Emesa_Target_Price">' . $data['Emesa_Target_Price'] . '</custom>
                <custom code="Emesa_Advice_Price">' . $data['Emesa_Advice_Price'] . '</custom>
                <custom code="emesa_market_category_id">' . $data['Emesa_Cat.'] . '</custom>
                <custom code="preferred_warehouse">' . $data['preferred_warehouse'] . '</custom>
                <custom code="type_item">' . $data['typeItem'] . '</custom>
                <custom code="Price_p_p_for_2">' . floatval(str_replace(',', '.',  $data['Price_p.p_for_2'])) . '</custom>
                <custom code="Price_p_p_for_3">' . floatval(str_replace(',', '.',  $data['Price_p.p_for_3'])) . '</custom>'



            . $this->callAttributeSet($attributeSet, $data)
            . $this->generateSimpleURLKey($data['description'], $data['itemCode']) .
            '</global>
			<website_codes>
				<item>base</item>
			</website_codes>
			<store_view code="default">
					<name>' . str_replace('&', '&#38;', $data['B2C_-_Title_2']) . '</name>
			</store_view>'
            . $this->getCategoriesByGC($categories) .
            '<stock>
			<qty>0</qty>
			<is_in_stock>0</is_in_stock>
			</stock>
		</simple>';
        return $xmlSimple;
    }

    private function generateConfigurableXML($sku, $name, $simple_skus, $categories, $attributeSet, $short_desc, $data)
    {
        $xmlConf = '<configurable sku="conf_' . $sku . '">
		<attribute_set_name>' . $attributeSet . '</attribute_set_name>
		<global>
			<name>' . str_replace('&', '&#38;', $name) . '</name>
			<price>0</price>
			<status>1</status>
			<visibility>4</visibility>
			<short_description>' . str_replace('&', '&#38;', htmlspecialchars($short_desc)) . '</short_description>
            <description>' . str_replace('&', '&#38;', htmlspecialchars($short_desc)) . '</description>
			<tax_class_name>Taxable Goods</tax_class_name>'
            . $this->generateURLKey($name, $sku)
            . $this->callAttributeSet($attributeSet, $data) .
            '</global>
		<stock>
			<is_in_stock>1</is_in_stock>
		</stock>
		<website_codes>
			<item>base</item>
		</website_codes>
		<store_view code="default">
					<name>' . str_replace('&', '&#38;', $name) . '</name>
		</store_view>'
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
            //$configName = $simpleArray[0]['Sitenaam'] . ' ' . $simpleArray[0]['color'];
            $configName = $simpleArray[0]['B2C_-_Title_2'];
            if (isset($simpleArray[0]['productCategories'])) {
                $categories = $simpleArray[0]['productCategories'];
            }
            if($simpleArray[0]['Artikelcode']!=null) {
                $attributeSets = ['FIS', 'PIL', 'QCS', 'QUI', 'TOP', 'TO-', 'TOC'];
                $artikelCode = substr($simpleArray[0]['Artikelcode'], 0, 3);
                if(in_array($artikelCode, $attributeSets)) {
                    if ($artikelCode == 'TO-') {
                        $artikelCode = 'TOP';
                    } elseif ($artikelCode == 'TOC') {
                        $artikelCode = 'FIS';
                    }
                }
                else{
                    $artikelCode = 'Default';
                }
            }
            else{
                $artikelCode = 'Default';
            }

            //$artikelCode = explode("-", $simpleArray[0]['Artikelcode']);
            //$np = explode(' ', $configName);
            //echo $configName = implode(" ", array_splice($np, 0, 3));
            //echo "<br>";
            $skus = '';

            foreach ($simpleArray as $key => $simple) {
                $xml .= $this->generateSimpleXML($simple, $artikelCode);
                $shortDesc = $simple['productTekst'];
                if($artGrpCode) {
                    $skus .= $this->prepareConfigurableSkus($simple['itemCode']);
                }
            }
            if($artGrpCode) {
                $xml .= $this->generateConfigurableXML($artGrpCode, $configName, $skus, $categories, $artikelCode, $shortDesc, $simpleArray[0]);
            }
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
        <select code="strijkvrij_fis">' . $this->yesNo($data['Strijkvrij_-_FIS']) . '</select>
        <select code="elastieke_rand_rondom_fis">' . $data['Elastieke_rand_rondom_-_FIS'] . '</select>
        <custom code="matrashoogte_fis">' . $data['Matrashoogte_-_FIS'] . '</custom>
        <custom code="hoekhoogte_fis">' . $data['Hoekhoogte_-_FIS'] . '</custom>
        <custom code="materiaal_fis">' . $data['Materiaal_-_FIS'] . '</custom>
        <custom code="maatvoering_fis">' . $data['Maatvoering-FIS'] . '</custom>
        <custom code="aantal_stuks_in_verpakking_fis">' . $data['Aantal_stuks_in_verpakking_-_FIS'] . '</custom>
        <custom code="product_lengte_fis">' . $data['Product_lengte_-_FIS'] . '</custom>
        <custom code="product_breedte_fis">' . $data['Product_breedte_-_FIS'] . '</custom>';

    }

    public function getPIL($data): string
    {

        return '<custom code="wasvoorschrift_pil">' . $data['Wasvoorschrift_-_PIL'] . '</custom>
        <select code="geschikt_voor_droger_pil">' . $data['Geschikt_voor_droger_-_PIL'] . '</select>
        <custom code="ritssluiting_pil">' . $data['Ritssluiting_-_PIL'] . '</custom>
        <custom code="anti_allergisch_pil">' . $data['Anti-allergisch_-_PIL'] . '</custom>
        <custom code="vulgewicht_pil">' . $data['Vulgewicht_-_PIL'] . '</custom>
        <custom code="vulling_pil">' . $data['Vulling_-_PIL'] . '</custom>
        <custom code="tijk_pil">' . $data['Tijk_-_PIL'] . '</custom>
        <custom code="lengte_pil">' . $data['Lengte_-_PIL'] . '</custom>
        <custom code="breedte_pil">' . $data['Breedte_-_PIL'] . '</custom>';
    }

    public function getQCS($data): string
    {

        return '<custom code="geschikt_voor_droger_qcs">' . $data['Geschikt_voor_droger_-_QCS'] . '</custom>
        <custom code="wasvoorschrift_qcs">' . $data['Wasvoorschrift_-_QCS'] . '</custom>
        <custom code="stijl_qcs">' . $data['Stijl_-_QCS'] . '</custom>
        <custom code="kussensloop_qcs">' . $data['Kussensloop_-_QCS'] . '</custom>
        <select code="instopstrook_qcs">' . $data['Instopstrook_-_QCS'] . '</select>
        <custom code="kwaliteit_qcs">' . $data['Kwaliteit_-_QCS'] . '</custom>
        <custom code="materiaal_qcs">' . $data['Materiaal_-_QCS'] . '</custom>
        <custom code="type_sluiting_qcs">' . $data['Type_Sluiting_-_QCS'] . '</custom>
        <custom code="lengte_qcs">' . $data['Lengte_-_QCS'] . '</custom>
        <custom code="breedte_qcs">' . $data['Breedte_-_QCS'] . '</custom>';
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
        <custom code="vulling_qui">' . $data['Vulling_-_QUI'] . '</custom>
        <custom code="lengte_qui">' . $data['Lengte_-_QUI'] . '</custom>
        <custom code="breedte_qui">' . $data['Breedte_-_QUI'] . '</custom>

        <custom code="productkenmerken_1_qui">' . $data['Productkenmerken_1_-_QUI'] . '</custom>';

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

    public function yesNo($v): string
    {
        $yesNo = [0=>'No', 1=>'Yes'];
        return $yesNo[$v];
    }
}
