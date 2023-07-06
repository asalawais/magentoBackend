<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Maxsel\ImportExportWP\Controller\Adminhtml\Index;

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
        $resultPage->setActiveMenu('Maxsel_ImportExportWP::index_index');
        $resultPage->getConfig()->getTitle()->prepend(__('ImportExportWP Sync'));
        return $resultPage;
    }
    /*protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Maxsel_ImportExportWP::index_index');
    }*/
}

