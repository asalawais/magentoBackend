<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace BigBridge\ProductImport\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey;
use Psr\Log\LoggerInterface;
use BigBridge\ProductImport\Model\Afas\Import as Process;

class Import implements HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $resultFactory;
    protected $request;
    protected $_session;
    protected $logger;
    protected $formKey;
    protected $import;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        LoggerInterface $loggerFile,
        ResultFactory $resultFactory,
        FormKey $formKey,
        Process $import
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->_session = $authSession;
        $this->logger = $loggerFile;
        $this->formKey = $formKey;
        $this->import = $import;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Collect data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
        public function execute()
    {
        if(!$this->_session->isLoggedIn()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
        $data = $this->request->getParams();
        $orderId = $data['id'];
        $formKey = $data['form_key'];
        if($orderId && $this->formKey==$formKey) {
            $order = $this->getOrderById($orderId);
            $result = $this->processOrder($order);
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['success' => $result]);
            //$this->logger->info(print_r($data, true));
        }
    }

    protected function processOrder($id){
        return $this->import->runImport($id);
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function getOrderById($id) {
        return $this->orderRepository->get($id);
    }
}

