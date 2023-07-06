<?php

namespace Twl\AdaptiveResize\Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class Gallery extends \Magento\Framework\App\Helper\AbstractHelper
{
  protected $galleryReadHandler;
  /**
   * Catalog Image Helper
   *
   * @var \Magento\Catalog\Helper\Image
   */
  protected $imageHelper;
  protected $w;
  protected $h;
  public function __construct(
  GalleryReadHandler $galleryReadHandler,  \Magento\Framework\App\Helper\Context $context,\Magento\Catalog\Helper\Image $imageHelper)
  {
      $this->imageHelper = $imageHelper;
      $this->galleryReadHandler = $galleryReadHandler;
      parent::__construct($context);
  }
 /** Add image gallery to $product */
  public function addGallery($product) {
      $this->galleryReadHandler->execute($product);
  }
  public function setWidthHeight($w, $h)
  {
    $this->w = $w;
    $this->h = $h;
  }
  public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
  {
      $imageUrls = array();
      $images = $product->getMediaGalleryImages();

      if ($images instanceof \Magento\Framework\Data\Collection) {
          foreach ($images as $image) {
              /** @var $image \Magento\Catalog\Model\Product\Image */
              $imageUrls[] = $this->imageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($image->getFile())
                        ->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(false)
                        ->keepFrame(true)
                        ->resize($this->w, $this->h)
                        ->getUrl();
              // if ($this->w && $this->h) {
              //   $imageUrls[] = $this->imageHelper->init($product, $image->getFile())
              //                    ->constrainOnly(TRUE)
              //                    ->keepAspectRatio(TRUE)
              //                    ->keepTransparency(false)
              //                    ->keepFrame(true)
              //                    ->resize($this->w, $this->h)->getUrl();
              // }else {
              //   $imageUrls[] = $this->imageHelper->init($product, 'product_page_image_small')
              //           ->setImageFile($image->getFile())
              //           ->getUrl();
              // }


              // $image->setData(
              //     'small_image_url',
              //     $this->imageHelper->init($product, 'product_page_image_small')
              //         ->setImageFile($image->getFile())
              //         ->getUrl()
              // );
              // $image->setData(
              //     'medium_image_url',
              //     $this->imageHelper->init($product, 'product_page_image_medium')
              //         ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
              //         ->setImageFile($image->getFile())
              //         ->getUrl()
              // );
              // $image->setData(
              //     'large_image_url',
              //     $this->imageHelper->init($product, 'product_page_image_large')
              //         ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
              //         ->setImageFile($image->getFile())
              //         ->getUrl()
              // );
          }
      }
      return $imageUrls;
  }

  public function getGalleryImagesAllSize(\Magento\Catalog\Api\Data\ProductInterface $product)
  {
      $imageUrls = $imageGallery = [];
      $images = $product->getMediaGalleryImages();
      $largeImage = $mediumImage = $smallImage = "";
      if ($images instanceof \Magento\Framework\Data\Collection) {
          foreach ($images as $image) {
              /** @var $image \Magento\Catalog\Model\Product\Image */

              $small = $this->imageHelper->init($product, 'product_page_image_small')
                      ->constrainOnly(true)
                      ->keepAspectRatio(true)
                      ->keepTransparency(false)
                      ->keepFrame(false)
                      ->setImageFile($image->getFile())
                      ->getUrl();
              $medium = $this->imageHelper->init($product, 'product_page_image_medium')
                  ->constrainOnly(true)
                  ->keepAspectRatio(true)
                  ->keepTransparency(false)
                  ->keepFrame(false)
                  ->setImageFile($image->getFile())
                  ->getUrl();
              $large = $this->imageHelper->init($product, 'product_page_image_medium') // product_page_image_large
                  ->constrainOnly(true)
                  ->keepAspectRatio(true)
                  ->keepTransparency(false)
                  ->keepFrame(false)
                  ->setImageFile($image->getFile())
                  ->getUrl();
              if ($smallImage == "") {
                $smallImage = $small;
              }
              if ($mediumImage == "") {
                $mediumImage = $medium;
              }
              if ($largeImage == "") {
                $largeImage = $large;
              }
              $imageGallery[$image->getId()] = [
                'small'=> $small,
                'medium' => $medium,
                'large' => $large
              ];


          }
          $imageUrls['large'] = $largeImage;
          $imageUrls['medium'] = $mediumImage;
          $imageUrls['small'] = $smallImage;
          $imageUrls['gallery'] = $imageGallery;
      }
      return $imageUrls;
  }
}
