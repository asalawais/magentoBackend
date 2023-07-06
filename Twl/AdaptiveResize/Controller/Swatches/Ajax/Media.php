<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Twl\AdaptiveResize\Controller\Swatches\Ajax;

// use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
// use Twl\AdaptiveResize\Helper\Gallery;
use Magento\Framework\App\ObjectManager;

class Media extends \Magento\Framework\App\Action\Action
{
    protected $_helper;

    public function __construct(
        Context $context
    ) {
        $this->_helper = ObjectManager::getInstance();
        parent::__construct($context);
    }

    public function execute()
    {
      /** @var \Magento\Framework\Controller\Result\Json $resultJson */
      $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

      /** @var \Magento\Framework\App\ResponseInterface $response */
      $response = $this->getResponse();

        $productMedia = $imagesUrls = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
          // $_helperGallery = $this->_helper('Twl\AdaptiveResize\Helper\Gallery');
          $_item = $this->_helper->get('Twl\AdaptiveResize\Helper\Data')->getLoadProduct($productId);
          $_helperGallery = $this->_helper->get('Twl\AdaptiveResize\Helper\Gallery');
          $_helperGallery->addGallery($_item);
          $imagesUrls = $_helperGallery->getGalleryImagesAllSize($_item);
        }



        // echo '<pre>I am in override controller';
        // var_dump($productId, $imagesUrls);
        // exit;
        //
        $resultJson->setData($imagesUrls);
        return $resultJson;
    }
}
