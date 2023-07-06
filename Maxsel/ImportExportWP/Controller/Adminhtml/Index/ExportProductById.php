<?php

/**
 * Created by PhpStorm.
 * User: benvansteenbergen
 * Date: 09/07/2018
 * Time: 09:23
 */

namespace Maxsel\ImportExportWP\Controller\Adminhtml\Index;

use Itonomy\ProductVisibilityGrid\Model\ProductIndexer;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Maxsel\ImportExportWP\Helper\Connect;

class ExportProductById extends \Magento\Backend\App\Action
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
        $productId = $this->getRequest()->getParam('id');
        try {
            $result = $this->connect->createProductById($productId);
        } catch (CouldNotSaveException $e) {
        } catch (InputException $e) {
        } catch (StateException $e) {
        }
        if ($result) {
            $this->messageManager->addSuccessMessage('Product id '. $productId.' is successfully exported to Wordpress '.$result);
        } else {
            $this->messageManager->addErrorMessage('Product id '. $productId.' is not able to export to Wordpress');
        }

        $this->_redirect('importexportwp/index/grid', ['store'=>$this->getRequest()->getParam('store')]);
    }
}
