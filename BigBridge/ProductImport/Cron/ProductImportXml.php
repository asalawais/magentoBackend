<?php

namespace BigBridge\ProductImport\Cron;

use BigBridge\ProductImport\Api\ImportConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use BigBridge\ProductImport\Model\Reader\XmlProductImporter;
use BigBridge\ProductImport\Model\Reader\ProductImportWebApiLogger;
use BigBridge\ProductImport\Helper\Indexer;
use Exception;
use XMLReader;

/**
 * @author Patrick van Bergen
 */
class ProductImportXml
{

    protected $_filesystem;
    protected $_xmlImport;
    protected $_indexer;

    public function __construct(
         \Magento\Framework\Filesystem $filesystem,
         XmlProductImporter $xmlImport,
         Indexer $indexer
    )
    {
        $this->_filesystem = $filesystem;
        $this->_xmlImport = $xmlImport;
        $this->_indexer = $indexer;
    }

    /**
     * Publish products
     *
     * @return void
     */
    public function execute()
    {
    	$var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
    	$xmlPath = $var.'import/afas_products.xml';
    	$logger = new ProductImportWebApiLogger();
    	$config = new ImportConfig();
    	$config->resultCallback = [$logger, 'productImported'];

    	//$output = new LoggerInterface();
    	//$output = new ProductImportLogger();
    	try {

        $this->_xmlImport->import($xmlPath, $config, false, $logger);
        $this->_indexer->reindexAll();
    	} catch (\Exception $e) {
            //$output->error($e->getMessage());
            //$output->error($e->getTraceAsString());
        }

    }



}
