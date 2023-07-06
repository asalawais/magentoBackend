<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BigBridge\ProductImport\Controller\Adminhtml\Import;
use BigBridge\ProductImport\Api\ImportConfig;
use BigBridge\ProductImport\Model\Reader\XmlProductImporter;
use BigBridge\ProductImport\Model\Reader\ProductImportWebApiLogger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use BigBridge\ProductImport\Helper\Indexer;
use BigBridge\ProductImport\Cron\AttributeValuesImporter;
use BigBridge\ProductImport\Cron\StockImporter;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;
class StockImport extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $loggerFile;
    private \Magento\Framework\Json\Helper\Data $jsonHelper;
    protected $_xmlImport;
    protected $_filesystem;
    private $attributesArray;
    private $stockArray;
    protected $resultFactory;
    protected $_session;
    protected $_indexer;


    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $loggerFile
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        PageFactory     $resultPageFactory,
        Json            $json,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        XmlProductImporter $xmlImport,
        LoggerInterface $loggerFile,
        AttributeValuesImporter $attributesArray,
        Indexer $indexer,
        StockImporter $stockArray,
        \Magento\Backend\Model\Auth\Session $authSession,
        ResultFactory $resultFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_filesystem = $filesystem;
        $this->jsonHelper = $jsonHelper;
        $this->serializer = $json;
        $this->_xmlImport = $xmlImport;
        $this->loggerFile = $loggerFile;
        $this->_indexer = $indexer;
        $this->attributesArray = $attributesArray;
        $this->stockArray = $stockArray;
        $this->resultFactory = $resultFactory;
        $this->_session = $authSession;
    }

    /**
     * Execute view action
     *
     * @return string
     */
    public function execute()
    {
        if(!$this->_session->isLoggedIn()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
        if($this->getRequest()->isAjax()) {
            //$this->logger->critical(print_r('dfdfdf', true));
            $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
            $xmlPath = $var.'import/afas_stock_products.xml';
            $logger = new ProductImportWebApiLogger();
            $success = true;
            $result = false;
            try {
                //$attr = $this->attributesArray->execute();
                $this->stockArray->execute();
                $this->loggerFile->critical('out called');
                    $this->loggerFile->critical('called');
                    $config = new ImportConfig();
                    $config->resultCallback = [$logger, 'productImported'];
                    $this->_xmlImport->import($xmlPath, $config, false, $logger);
                    if ($logger->getFailedProductCount() > 0) {
                        $success = false;
                    }
                    $this->_indexer->reindexAll();
            } catch (LocalizedException $e) {
                $success = false;
            } catch (\Exception $e) {
                $this->loggerFile->critical($e);
                $success = false;
            }
            if (!$success) {
                $result .= "\n";
                $result .= "Error in " . basename($xmlPath) . ":\n";
                $result .= $logger->getOutput() . "\n";
            } elseif ($logger->getOkProductCount() == 0) {
                $result .= $logger->getOutput();
            }
            //return $this->jsonResponse(['success'=>true, 'result'=>$result]);
        }
        return false;
    }

    /**
     * Create json response
     *
     * @return ResultInterface
     */
    public function jsonResponse($response = [])
    {
        /*$this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json');
        return $this->http->setBody(
            $this->serializer->serialize($response)
        );*/
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }


}

