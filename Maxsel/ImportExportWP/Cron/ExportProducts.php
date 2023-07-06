<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace Maxsel\ImportExportWP\Cron;

use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use Automattic\WooCommerce\Client;
use function GuzzleHttp\Promise\exception_for;

/**
 * Class ProductPublisher
 */
class ExportProducts
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var ProductRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var Client
     */
    private $clientWP;

    /**
     * @var ConfigInterface
     */
    private $config;

    private $directoryList;
    protected $logger;
    protected $_filesystem;
    protected $_file;

    /**
     * ProductPublisher constructor.
     *
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ProductRepositoryFactory $repositoryFactory
     * @param ProductsArray $productsArray
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */
    public function __construct(
        SearchCriteriaBuilderFactory              $criteriaBuilderFactory,
        ProductRepositoryFactory                  $repositoryFactory,
        StoreManagerInterface                     $storeManager,
        \Psr\Log\LoggerInterface                  $logger,
        \Magento\Framework\Module\Dir\Reader      $moduleReader,
        \Magento\Framework\Filesystem             $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file
        //ConfigInterface $config,
        //Client                                    $clientWP
    )
    {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        //$this->clientWP = $clientWP;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->logger = $logger;
        //$this->config = $config;
    }

    /**
     * Publish products
     *
     * @return bool
     */
    public function execute()
    {
        try {
            $this->createProducts();
        } catch (CouldNotSaveException $e) {
        } catch (InputException $e) {
        } catch (StateException $e) {
        }

    }


    private function clientWP(){
        return new Client(
            'http://boxspringgektenieuw.sampreview.nl',
            'ck_a358d6783be703f4845b48cb6c0e0ea20abdac75',
            'cs_1a618c09a004caffc8ee1f1904cf9d522a7f67b4',
            [
                'wp_api'  => true,
                'version' => 'wc/v3',
            ]
        );
        /*return new Client(
            'http://wp-api.local',
            'ck_9c4be04b563fe08ac7de6643ee25dd18e04bc8c1',
            'cs_a5937d320a69a68da8a3efd090b4175ebaee78b7',
            [
                'wp_api'  => true,
                'version' => 'wc/v3',
            ]
        );*/
    }

    /**
     * @return void
     * @throws InputException
     * @throws StateException
     *
     * @throws CouldNotSaveException
     */
    private function createProducts()
    {
        $data = [
            'name' => 'Premium Quality',
            'type' => 'simple',
            'regular_price' => '21.99',
            'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
            'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            'categories' => [
                [
                    'id' => 9
                ],
                [
                    'id' => 14
                ]
            ],
            'images' => [
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                ],
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
                ]
            ]
        ];

       $result =  $this->clientWP()->post('products', $data);
       print_r($result);

    }
}
