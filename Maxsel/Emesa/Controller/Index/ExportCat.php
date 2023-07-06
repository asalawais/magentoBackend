<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\Emesa\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Maxsel\Emesa\Helper\Connect;

class ExportCat extends \Magento\Framework\App\Action\Action
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
    protected $logger;
    /**
     * @var Http
     */
    protected $http;
    /**
     * @var Connect
     */
    protected $connect;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        Connect $connect,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->_filesystem = $filesystem;
        $this->connect = $connect;
        $this->http = $http;
        return parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->connect->getCategoryList();
            $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
            $fp = fopen($var.'export/market_categories.csv', 'w');
            foreach ($result->getData() as $line ) {
                fputcsv($fp, array($line->getName(), $line->getMarketCategoryId(), $line->getCanContainProducts(), $line->getCommissionPercentage()));
            }
            fclose($fp);
            //$result = $this->connect->getlistProducts();
            //$result = $this->connect->getShippingClasses();
            //return $this->jsonResponse('products export');
            //return $this->jsonResponse($result);
        } catch (LocalizedException $e) {
            //return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            //return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return ResultInterface
     */
    public function jsonResponse($response = '')
    {
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json');
        return $this->http->setBody(
            $this->serializer->serialize($response)
        );
    }
}

