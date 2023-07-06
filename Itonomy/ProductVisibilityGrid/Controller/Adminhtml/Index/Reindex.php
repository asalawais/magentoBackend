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

class Reindex extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultRedirectFactory;

    protected $productIndexer;

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
        ProductIndexer $productIndexer,
        Connect $connect
    ) {

        $this->productIndexer = $productIndexer;
        $this->messageManager = $messageManager;
        $this->connect = $connect;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\ForwardFactory
     */
    public function execute()
    {
        $shippingClassId = $this->getRequest()->getParam('shipping');
        $productIdsForIndexing = [$this->getRequest()->getParam('id')];
        //$indexResult = $this->productIndexer->reindexList($productIdsForIndexing);
        $result = $this->connect->putOffer($productIdsForIndexing, $shippingClassId);
        if ($result) {
            $this->messageManager->addSuccessMessage('Product id '.join($productIdsForIndexing).' is succesfully scheduled for offer');
        } else {
            $this->messageManager->addErrorMessage('Product id '.join($productIdsForIndexing).' is not able to be sent to offer');
        }

        $this->_redirect('productvisibility/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
