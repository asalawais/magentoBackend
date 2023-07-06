<?php

/**
 * Created by PhpStorm.
 * User: benvansteenbergen
 * Date: 09/07/2018
 * Time: 09:23
 */

namespace Maxsel\ImportExportWP\Controller\Adminhtml\Index;

use Itonomy\ProductVisibilityGrid\Model\ProductIndexer;
use Maxsel\ImportExportWP\Helper\Connect;

class MassExport extends \Magento\Backend\App\Action
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
        $productIds = $this->getRequest()->getParam('product');
        if (count($productIds) > 100) {
            $this->messageManager->addErrorMessage('Hey! Take it ease administrator, we can only handle so much as a 100 products!');
        }
        $result = $this->connect->createProducts($productIds);

        if ($result) {
            $this->messageManager->addSuccessMessage('Product id '.join($productIds).' are successfully scheduled for Wordpress Export');
        } else {
            $this->messageManager->addErrorMessage('Product id '.join($productIds).' are not able to be sent to Wordpress');
        }

        $this->_redirect('importexportwp/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
