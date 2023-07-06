<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace BigBridge\ProductImport\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $resultFactory;
    protected $_session;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(PageFactory $resultPageFactory, \Magento\Backend\Model\Auth\Session $authSession, ResultFactory $resultFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->_session = $authSession;
    }

    /**
     * Execute view action
     */
        public function execute()
    {
        if(!$this->_session->isLoggedIn()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('BigBridge_ProductImport::afas');
            $resultPage->getConfig()->getTitle()->prepend(__('Product Import'));
            return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('BigBridge_ProductImport::afas');
    }
}

