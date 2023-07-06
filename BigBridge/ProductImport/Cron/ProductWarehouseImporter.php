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
class ProductWarehouseImporter //implements CronJobInterface
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
            <custom code="A_Lengte_Van_De_Langere_Kant_Van_Het_Artikel">' . $data['A-Lengte_Van_De_Langere_Kant_Van_Het_Artikel'] . '</custom>
            <custom code="A_Breedte_Van_De_Kortere_Kant_Van_Het_Artikel">' . $data['A-Breedte_Van_De_Kortere_Kant_Van_Het_Artikel'] . '</custom>
    <custom code="A_Itemtype_Naam">' . $data['A-Itemtype_Naam'] . '</custom>
    <custom code="A_Materiaal_Buitenkant_1">' . $data['A-Materiaal_Buitenkant_1'] . '</custom>
    <custom code="A_Type_Binnenmateriaal">' . $data['A-Type_Binnenmateriaal'] . '</custom>
    <custom code="A_Productkenmerken_1">' . $data['A-Productkenmerken_1'] . '</custom>
    <custom code="A_Materiaal_Type">' . $data['A-Materiaal_Type'] . '</custom>
    <custom code="A_Elastieke_rand_rondom">' . $data['A-Elastieke_rand_rondom'] . '</custom>
    <custom code="A_Hoekhoogte">' . $data['A-Hoekhoogte'] . '</custom>
    <custom code="A_Matrashoogte">' . $data['A-Matrashoogte'] . '</custom>
    <custom code="A_Strijkvrij">' . $data['A-Strijkvrij'] . '</custom>
    <custom code="A_Waterdicht">' . $data['A-Waterdicht'] . '</custom>
    <custom code="A_Wasvoorschrift">' . $data['A-Wasvoorschrift'] . '</custom>
    <custom code="A_Geschikt_voor_droger">' . $data['A-Geschikt_voor_droger'] . '</custom>
    <custom code="A_Draaddichtheid">' . $data['A-Draaddichtheid'] . '</custom>
    <custom code="A_Maatvoering">' . $data['A-Maatvoering'] . '</custom>
    <custom code="A_Aantal_items">' . $data['A-Aantal_items'] . '</custom>
    <custom code="A_Type_stof">' . $data['A-Type_stof'] . '</custom>
    <custom code="A_Instructies_voor_productverzorging">' . $data['A-Instructies_voor_productverzorging'] . '</custom>
    <custom code="A_Grootte">' . $data['A-Grootte'] . '</custom>
    <custom code="A_Afmetingen_van_item_L">' . $data['A-Afmetingen_van_item_L'] . '</custom>
    <custom code="A_Afmetingen_van_item_B">' . $data['A-Afmetingen_van_item_B'] . '</custom>
    <custom code="A_Afmetingen_van_item_H">' . $data['A-Afmetingen_van_item_H'] . '</custom>
    <custom code="A_Productkenmerken_2">' . $data['A-Productkenmerken_2'] . '</custom>
    <custom code="A_Productkenmerken_3">' . $data['A-Productkenmerken_3'] . '</custom>
    <custom code="A_Productkenmerken_4">' . $data['A-Productkenmerken_4'] . '</custom>
    <custom code="A_Productkenmerken_5">' . $data['A-Productkenmerken_5'] . '</custom>
    <custom code="A_Fabrikant">' . $data['A-Fabrikant'] . '</custom>
    <custom code="AFR_Title">' . $data['AFR-_Title'] . '</custom>
    <custom code="AFR_Type_Artikel">' . $data['AFR_-_Type_Artikel'] . '</custom>
    <custom code="AFR_Productkenmerken_1">' . $data['AFR_-_Productkenmerken_1'] . '</custom>
    <custom code="AFR_Productkenmerken">' . $data['AFR_-_Productkenmerken'] . '</custom>
    <custom code="AFR_Materiaalsoort">' . $data['AFR_-_Materiaalsoort'] . '</custom>
    <custom code="AFR_Elastische_rand_rondom">' . $data['AFR_-_Elastische_rand_rondom'] . '</custom>
    <custom code="AFR_Hoekhoogte">' . $data['AFR_-_Hoekhoogte'] . '</custom>
    <custom code="AFR_Kleur">' . $data['AFR_-_Kleur'] . '</custom>
    <custom code="AFR_Strijk_vrij">' . $data['AFR_-_Strijk_vrij'] . '</custom>
    <custom code="AFR_Waterdicht">' . $data['AFR_-_Waterdicht'] . '</custom>
    <custom code="AFR_Wasvoorschrift">' . $data['AFR_-_Wasvoorschrift'] . '</custom>
    <custom code="AFR_Geschikt_voor_droger">' . $data['AFR_-_Geschikt_voor_droger'] . '</custom>
    <custom code="AFR_Grootte">' . $data['AFR_-_Grootte'] . '</custom>
    <custom code="AFR_Soort_stof">' . $data['AFR_-_Soort_stof'] . '</custom>
    <custom code="AFR_Instructies_voor_productverzorging">' . $data['AFR_-_Instructies_voor_productverzorging'] . '</custom>
    <custom code="AFR_Productkenmerken_2">' . $data['AFR_-_Productkenmerken_2'] . '</custom>
    <custom code="AFR_Productkenmerken_3">' . $data['AFR_-_Productkenmerken_3'] . '</custom>
    <custom code="AFR_Productkenmerken_4">' . $data['AFR_-_Productkenmerken_4'] . '</custom>
    <custom code="AFR_Productkenmerken_5">' . $data['AFR_-_Productkenmerken_5'] . '</custom>
    <custom code="ADE_Title">' . $data['ADE-Title'] . '</custom>
    <custom code="ADE_Itemtype_Naam">' . $data['ADE-Itemtype_Naam'] . '</custom>
    <custom code="ADE_Productkenmerken">' . $data['ADE-Productkenmerken'] . '</custom>
    <custom code="ADE_Materiaal_Type">' . $data['ADE-Materiaal_Type'] . '</custom>
    <custom code="ADE_Elastieke_rand_rondom">' . $data['ADE-Elastieke_rand_rondom'] . '</custom>
    <custom code="ADE_Kleur">' . $data['ADE-Kleur'] . '</custom>
    <custom code="ADE_Wasvoorschrift">' . $data['ADE-Wasvoorschrift'] . '</custom>
    <custom code="ADE_Geschikt_voor_droger">' . $data['ADE-Geschikt_voor_droger'] . '</custom>
    <custom code="ADE_Maatvoering">' . $data['ADE-Maatvoering'] . '</custom>
    <custom code="ADE_Type_stof">' . $data['ADE-Type_stof'] . '</custom>
    <custom code="ADE_Instructies_voor_productverzorging">' . $data['ADE-Instructies_voor_productverzorging'] . '</custom>
    <custom code="ADE_Productkenmerken_2">' . $data['ADE-Productkenmerken_2'] . '</custom>
    <custom code="ADE_Productkenmerken_3">' . $data['ADE-Productkenmerken_3'] . '</custom>
    <custom code="ADE_Productkenmerken_4">' . $data['ADE-Productkenmerken_4'] . '</custom>
    <custom code="ADE_Productkenmerken_5">' . $data['ADE-Productkenmerken_5'] . '</custom>
    <custom code="Emesa_Target_Price">' . $data['Emesa_Target_Price'] . '</custom>
    <custom code="Emesa_Advice_Price">' . $data['Emesa_Advice_Price'] . '</custom>
    <custom code="emesa_market_category_id">' . $data['Emesa_Cat.'] . '</custom>
    <custom code="preferred_warehouse">' . $data['preferred_warehouse'] . '</custom>
    <custom code="ADE_Product_tekst">' . $data['ADE-Product_tekst'] . '</custom>'

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
                if($artGrpCode) {
                    $skus .= $this->prepareConfigurableSkus($simple['itemCode']);
                }
            }
            if($artGrpCode) {
                $xml .= $this->generateConfigurableXML($artGrpCode, $configName, $skus, $categories, $artikelCode);
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
        <select code="strijkvrij_fis">' . $data['Strijkvrij_-_FIS'] . '</select>
        <select code="elastieke_rand_rondom_fis">' . $data['Elastieke_rand_rondom_-_FIS'] . '</select>
        <custom code="matrashoogte_fis">' . $data['Matrashoogte_-_FIS'] . '</custom>
        <custom code="hoekhoogte_fis">' . $data['Hoekhoogte_-_FIS'] . '</custom>
        <custom code="materiaal_fis">' . $data['Materiaal_-_FIS'] . '</custom>
        <custom code="geschikt_voor_droger_fis_yn">' . $data['Geschikt_voor_droger_-FIS_YN'] . '</custom>
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
        <custom code="breedte_qcs">' . $data['Breedte_-_QCS'] . '</custom>
        <custom code="productkenmerken_qcs">' . $data['Productkenmerken_-_QCS'] . '</custom>
        <custom code="materiaal_amazon_qcs">' . $data['Materiaal_Amazon-QCS'] . '</custom>';
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
        <custom code="materiaal_buitenkant_qui">' . $data['Materiaal_Buitenkant-QUI'] . '</custom>
        <custom code="materiaal_buitenkant2_qui">' . $data['Materiaal_Buitenkant2-QUI'] . '</custom>
        <custom code="materiaal_binnenkant_qui">' . $data['Materiaal_Binnenkant-QUI'] . '</custom>
        <custom code="materiaal_binnenkant2_qui">' . $data['Materiaal_Binnenkant2-QUI'] . '</custom>
        <custom code="productkenmerken_1_qui">' . $data['Productkenmerken_1_-_QUI'] . '</custom>
        <custom code="product_kenmerken_qui">' . $data['Product_kenmerken-QUI'] . '</custom>';

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
