<?php
/**
 * Copyright © 2015 RokanThemes.com. All rights reserved.

 */
namespace BigBridge\ProductImport\Controller\Index;

/**
 * Blog home page view
 */

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class AssignImages extends \Magento\Framework\App\Action\Action
{

	/**
     * Product $product
     */
    private $product;
    private $productCollection;
    private $galleryImages;
    private $productRepository;
    private $productFactory;
    protected $_productRepositoryFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        Product $product,
        ReadHandler $galleryImages,
        Collection $productCollection,
        ProductRepositoryInterface $productRepository,
        DirectoryList $mediaPath ,
        CollectionFactory  $productFactory    ,
         \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {
        $this->product = $product;
        $this->galleryImages = $galleryImages;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->mediaPath = $mediaPath;
        $this->productFactory = $productFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        return parent::__construct($context);
    }



    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

    	$this->getConfigurableProducts();
    }

    public function getConfigurableProducts() {
	$collection = $this->productFactory->create();
	$collection->addAttributeToSelect('*');
	$collection->addAttributeToFilter('type_id', ['eq' => 'configurable']);
	//$collection->addStoreFilter(1);

	 echo count($collection);
		foreach($collection as $confProduct){

			//echo $confProduct->getTypeId();
		     if($confProduct->getTypeId() == "configurable"){
		     	//echo $confProduct->getTypeId();
		     	/*	$confProduct = $this->product->load($confProduct->getId());
		     	$existingMediaGalleryEntries = $confProduct->getMediaGalleryEntries();
		     	if(count($existingMediaGalleryEntries)>0){
				foreach ($existingMediaGalleryEntries as $key => $entry) {
				    unset($existingMediaGalleryEntries[$key]);
				}
					$confProduct->setMediaGalleryEntries($existingMediaGalleryEntries);
					$this->productRepository->save($confProduct);
				}*/



				$productConf = $this->_productRepositoryFactory->create()->getById($confProduct->getId());
				//echo $productConf->getData('image');
				//if(!$productConf->getData('image')){
		     	$confImages = [];
		          $children = $confProduct->getTypeInstance()->getUsedProducts($confProduct);
		          //echo count($children);
		          if(count($children)>0){
		          	//print_r($children);
		              foreach ($children as $child){
		               	//echo $child->getSku();
						//echo "<br>";
		               	//$product = $this->product->load($child->getId());

		               	$product = $this->_productRepositoryFactory->create()->getById($child->getId());
								$image = $product->getData('image');
								if($image){
								  //echo  $product->getData('image');
								  //echo "<br>";
								//$product->getData('thumbnail');
								//$product->getData('small_image');

		                    //$this->galleryImages->execute($product);
		                //$images = $product->getMediaGalleryImages();
		                //foreach($images as $img) {
		                	//echo $img->getPath();
		                	//echo "<br>";
		                	//$confImages[$confProduct->getSku()][] = $img->getFile();
		                	$path = $this->mediaPath->getPath('media').'/catalog/product'.$product->getData('image');
		                	if (file_exists($path)) {
		                	$confProduct->addImageToMediaGallery($path, array('image', 'small_image', 'thumbnail'), false, false);

		                }
		                        //$productGallery->deleteGallery($img->getValueId());
		                        //$imageProcessor->removeImage($product, $img->getFile());
		                   //}

		        }

		    break;
		}
		          $confProduct->save();
		        // }
		        }
		          //print_r($confImages);

		          //die;

		}
		}
	 }

}
