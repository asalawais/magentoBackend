<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Helper;

//use BigBridge\ProductImport\System\ConfigInterface;

//use PracticalAfas\Client\RestCurlClient;
use Bigbridge\ProductImport\Client\RestCurlClient;
use BigBridge\ProductImport\Helper\Config;
/**
 * Class ProductPublisher
 */
class RestCurlClientConfig //implements CronJobInterface
{

	protected $logger;
	protected $config;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Config $config

    ) {
        $this->logger = $logger;
        $this->config = $config;


    }

	public function getClient(){

       $client = new RestCurlClient(
        [ 'customerId' => 89626, 'appToken' => $this->config->getAfasToken(), 'environment'=>'test'] );
        //[ 'customerId' => 89626, 'appToken' => $this->config->getAfasToken()] );

       return $client;

	}
}
