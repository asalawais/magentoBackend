<?php

namespace Maxsel\Brouwers\Helper;

use BigBridge\ProductImport\Helper\RestCurlClientConfig;
use PracticalAfas\Client\RestCurlClient;

class Data extends \BigBridge\ProductImport\Helper\CurlFetch {

    protected $logger;
    protected $_restCurlClient;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        RestCurlClientConfig     $restCurlClient

    )
    {
        $this->logger = $logger;
        $this->_restCurlClient = $restCurlClient;


    }

    public function getMendrixRows()
    {
        //$client = $this->_restCurlClient->getClient();
        $client = new RestCurlClient( [ 'customerId' => 89626, 'appToken' => '3E89D236B4414A3A9C2DD07C5FAC7911ADE9DDB740E1682BAE4FE2A57A9158DF' ] );

        $get = $client->callAfas(
            'GET',
            'connectors/Pakbon_Nieuwhoff_Export',
            [
                'take' => 100,
                'filterfieldids' => 'Exporteren_naar_Nieuwhoff,Geexporteerd_naar_Nieuwhoff',
                'filtervalues' => 'true,false',
                'operatortypes' => '1,1'
            ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

}
