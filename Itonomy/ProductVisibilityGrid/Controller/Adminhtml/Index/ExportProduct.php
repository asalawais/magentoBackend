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

class ExportProduct extends \Magento\Backend\App\Action
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
        $ids = [];
        if($this->getRequest()->getParam('id')){
            $productIdsForIndexing = $this->getRequest()->getParam('id');
            $ids[] = $productIdsForIndexing;
        }
        else {
            $productIdsForIndexing = $this->getRequest()->getParam('product');
            $ids = $productIdsForIndexing;
        }
        //$indexResult = $this->productIndexer->reindexList($productIdsForIndexing);
        $result = $this->connect->putProductsByIds($productIdsForIndexing);

        if (isset($result['id']) && count($result['id'])>0) {
            $this->messageManager->addSuccessMessage('Product id '. implode(',',$ids).' is/are succesfully exported');
        }
        if (isset($result['error']) && count($result['error'])>0) {
            $this->messageManager->addErrorMessage('Product id '. implode(',',$ids).' is/are not able to export'. implode(',', $result['error']));
        }

        $this->_redirect('productvisibility/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
