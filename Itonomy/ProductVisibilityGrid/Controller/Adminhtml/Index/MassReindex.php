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

class MassReindex extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultRedirectFactory;

    protected $productIndexer;

    protected $messageManager;

    Protected $connect;

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
        $productIdsForIndexing = $this->getRequest()->getParam('product');
        $shippingClassId = $this->getRequest()->getParam('shipping');
        if (count($productIdsForIndexing) > 100) {
            $this->messageManager->addErrorMessage('Hey! Take it ease administrator, we can only handle so much as a 100 products!');
        }

        //$indexResult = $this->productIndexer->reindexList($productIdsForIndexing);
        $result = $this->connect->putOffer($productIdsForIndexing, $shippingClassId);

        if ($result) {
            $this->messageManager->addSuccessMessage('Product id '.join($productIdsForIndexing).' are succesfully scheduled for offers');
        } else {
            $this->messageManager->addErrorMessage('Product id '.join($productIdsForIndexing).' are not able to be sent to offers');
        }

        $this->_redirect('productvisibility/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
