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

/**
 * Class ProductPublisher
 */
class GalleryImporterTwo //implements CronJobInterface
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

    protected $_filesystem;

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
        //ConfigInterface $config,
        CurlFetch $productsArray

    ) {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->productsArray = $productsArray;
        $this->directoryList = $directoryList;
        $this->_filesystem = $filesystem;
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
        $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        //$skip = 0;
        $c = 50;
        foreach (range(1000, 2000, 50) as $skip) {
            $take =  $c+$skip;

        $rows = $this->productsArray->getGalleryRows($skip, $take);
        if(count($rows) > 0){
        //echo print_r($rows);

         $content = '';
            foreach ($rows as $value) {
                //echo $value['img1_name'];
                $this->base64_to_jpeg($value['img1'], $var.'import/images/'.$value['img1_name']);

                if(!is_null($value['img2_name']))
                $this->base64_to_jpeg($value['img2'], $var.'import/images/'.$value['img2_name']);

                if(!is_null($value['img3_name']))
                $this->base64_to_jpeg($value['img3'], $var.'import/images/'.$value['img3_name']);

                if(!is_null($value['img4_name']))
                $this->base64_to_jpeg($value['img4'], $var.'import/images/'.$value['img4_name']);

                if(!is_null($value['img5_name']))
                $this->base64_to_jpeg($value['img5'], $var.'import/images/'.$value['img5_name']);

                if(!is_null($value['img6_name']))
                $this->base64_to_jpeg($value['img6'], $var.'import/images/'.$value['img6_name']);

                if(!is_null($value['img7_name']))
                $this->base64_to_jpeg($value['img7'], $var.'import/images/'.$value['img7_name']);

                $content .= $this->getImages($value, $value['itemCode']);
            }
                    $fileName = $var.'import/afas_images'.$take.'.xml';
                    $myfile = fopen($fileName, "w") or die("Unable to open file!");
                    $importXML = '<?xml version="1.0" encoding="UTF-8"?>
                                        <import>'.$content.'</import>';
                    try {
                      fwrite($myfile, $importXML);
                      fclose($myfile);
                    } catch (Exception $e) {

                    }
             /*$dir = $this->directoryList->getPath('var');
                    $fileName = 'var/import/afas_products.xml';
                    $myfile = fopen($fileName, "w") or die("Unable to open file!");
                    try {
                      fwrite($myfile, $content);
                      fclose($myfile);
                    } catch (Exception $e) {

                    }
                    return;*/

                    //return $importXML;
                }
        }



    }



private function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' );

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = $base64_string;

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data ) );

    // clean up the file resource
    fclose( $ifp );

    return $output_file;
}


private function getImages($img, $sku){
    $xml = '<simple sku="'.$sku.'">
         <attribute_set_name>Default</attribute_set_name>
          <images>
            <image file_or_url="images/'.$img['img1_name'].'">
                <global>
                    <role>image</role>
                    <role>small_image</role>
                    <role>thumbnail</role>
                    <gallery_information label="MPS Textiles" position="1" enabled="1" />
                </global>
            </image>'
        . $this->getChildImages($img['img2_name'])
        . $this->getChildImages($img['img3_name'])
        . $this->getChildImages($img['img4_name'])
        . $this->getChildImages($img['img5_name'])
        . $this->getChildImages($img['img6_name'])
        . $this->getChildImages($img['img7_name'])
        .
        '</images>
    </simple>';

    return $xml;

}

    private function getChildImages($img){
        $xml = '';
        if(!is_null($img)) {
            $xml = '<image file_or_url="images/' . $img . '">
                <global>
                    <gallery_information label="MPS Textiles" position="1" enabled="1" />
                </global>
            </image>';
        }
        return $xml;

    }


}
