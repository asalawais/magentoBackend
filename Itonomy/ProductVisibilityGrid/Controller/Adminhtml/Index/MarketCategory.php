<?php

/**
 * Created by PhpStorm.
 * User: benvansteenbergen
 * Date: 09/07/2018
 * Time: 09:23
 */

namespace Itonomy\ProductVisibilityGrid\Controller\Adminhtml\Index;

use Itonomy\ProductVisibilityGrid\Model\ProductIndexer;
use Maxsel\Emesa\Helper\Connect;

class MarketCategory extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultRedirectFactory;

    protected $productIndexer;


    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    protected $messageManager;
    protected $connect;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ProductIndexer $productIndexer,
        Connect $connect
    ) {

        $this->productIndexer = $productIndexer;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->connect = $connect;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\ForwardFactory
     */
    public function execute()
    {
        $productIdsForIndexing = $this->getRequest()->getParam('id');
        //$indexResult = $this->productIndexer->reindexList($productIdsForIndexing);
        $result = $this->connect->putProductByMarketCategoryId($productIdsForIndexing);
        if (isset($result['market_category_id'])) {
            $this->messageManager->addSuccessMessage('Product id '. $productIdsForIndexing.' is succesfully assigned to Market Category ID '.$result);
        } else {
            $this->messageManager->addErrorMessage('Product id '. $productIdsForIndexing.' is not able to assign to Market Category');
        }

        $this->_redirect('productvisibility/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
