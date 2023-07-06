<?php

namespace BigBridge\ProductImport\Helper;

//use PracticalAfas\Client\RestCurlClient;
/**
 * @author Patrick van Bergen
 */
class CurlFetch
{
    protected $logger;
    protected $_restCurlClient;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        RestCurlClientConfig $restCurlClient
    ) {
        $this->logger = $logger;
        $this->_restCurlClient = $restCurlClient;
    }
    /* public static function getProductRows(){
      $url = 'https://89626.rest.afas.online/ProfitRestServices/connectors/magento_article';
      $query = '?filterfieldids=b2b&filtervalues=true&operatortypes=1&skip=-1&take=-1';
      //$encoded = base64_encode($token);
      //$authValue = "AfasToken {$encoded}";
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url.$query);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: AfasToken PHRva2VuPjx2ZXJzaW9uPjE8L3ZlcnNpb24+PGRhdGE+NDRERDNGMjZCODkwNDU2MTkwMkREMTBDRTE4QjdCODczQjBDODAwQTRGRjUxQTE4QzBGQjk3MDBCQkFDMkE3QjwvZGF0YT48L3Rva2VuPg=='
      ));

      $productList = curl_exec($ch);
      curl_errno($ch);

      if(curl_errno($ch)) {
      echo 'Curl error: ' . curl_error($ch);

      } else {
      $response = json_decode($productList, true);

      }

      curl_close($ch);
      return $response['rows'];
      } */

    /*public static function getGalleryRows($s, $t) {
        $url = 'https://89626.rest.afas.online/ProfitRestServices/connectors/magento_articleimage';
        $query = '?skip=' . $s . '&take=' . $t;
        //$encoded = base64_encode($token);
        //$authValue = "AfasToken {$encoded}";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: AfasToken PHRva2VuPjx2ZXJzaW9uPjE8L3ZlcnNpb24+PGRhdGE+NDRERDNGMjZCODkwNDU2MTkwMkREMTBDRTE4QjdCODczQjBDODAwQTRGRjUxQTE4QzBGQjk3MDBCQkFDMkE3QjwvZGF0YT48L3Rva2VuPg=='
        ));

        $galleryList = curl_exec($ch);
        curl_errno($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            $response = json_decode($galleryList, true);
        }
        curl_close($ch);
        return $response['rows'];
    }*/

    public function getGalleryRows($s, $t)
    {
        $client = $this->_restCurlClient->getClient();

        $this->logger->debug('Products Gallery Importer: start');

        $get = $client->callAfas(
            'GET',
            'connectors/magento_articleimage',
            [
                    'skip' => $s,
                    'take' => $t
                ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

    public function getProductRows()
    {
        $client = $this->_restCurlClient->getClient();

        $this->logger->debug('Products Importer: start');

        /*$get = $client->callAfas(
            'GET',
            'connectors/magento_article',
            [
                    'take' => 2000,
                    'filterfieldids' => 'b2c,Artikelgroep,Consumer_Price',
                    'filtervalues' => 'true,8020',
                    'operatortypes' => '1,1,9'
                ]
        );*/
        $get = $client->callAfas(
            'GET',
            'connectors/magento_article',
            [
                'take' => 2000,
                'filterfieldids' => 'b2c,Consumer_Price',
                'filtervalues' => 'true',
                'operatortypes' => '1,9'
            ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

    public function getProductGroupedRows()
    {
        $client = $this->_restCurlClient->getClient();
        //$client = new RestCurlClient( [ 'customerId' => 89626, 'appToken' => '3E89D236B4414A3A9C2DD07C5FAC7911ADE9DDB740E1682BAE4FE2A57A9158DF' ] );
        $this->logger->debug('Products Grouped Importer: start');

        $get = $client->callAfas(
            'GET',
            'connectors/Products_configurable',
            [
                    'take' => 2000
                ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

    public function getProductSingleRow($itemCode)
    {
        $client = $this->_restCurlClient->getClient();

        $this->logger->debug('Product Single Importer: start');
        $filterValues = 'true,8010,' . $itemCode;
        $get = $client->callAfas(
            'GET',
            'connectors/magento_article',
            [
                    'take' => 1,
                    'filterfieldids' => 'itemCode',
                    'filtervalues' => $itemCode,
                    'operatortypes' => '1'
                ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult['rows']);

        if (isset($dataResult['rows']) && !empty($dataResult['rows'])) {
            return $dataResult['rows'][0]['qty_explosion'];
        }
        return null;
    }

    public function getStockRows($stockSource)
    {
        $client = $this->_restCurlClient->getClient();

        $this->logger->debug('Stock Importer: start');

        $get = $client->callAfas(
            'GET',
            'connectors/Voorraad__virtueel__per_magazijn',
            [
                    'take' => 2000,
                    'filterfieldids' => 'Magazijn',
                    'filtervalues' => $stockSource,
                    'operatortypes' => '1'
                ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

    public function getStockFeedRows()
    {
        $client = $this->_restCurlClient->getClient();

        $this->logger->debug('Stock Feed Importer: start');

        $get = $client->callAfas(
            'GET',
            'connectors/magento_stock_FEED',
            [
                    'take' => 2000,
                    'filterfieldids' => 'b2b',
                    'filtervalues' => 'true',
                    'operatortypes' => '1'
                ]
        );
        $dataResult = json_decode($get, true);
        //print_r($dataResult);
        return $dataResult['rows'];
    }

    public function setDebtor($customer, $address)
    {
        $client = $this->_restCurlClient->getClient();
        $data = $this->setInfo($customer, $address);
        $get = $client->callAfas(
            'POST',
            'connectors/KnSalesRelationOrg',
            [],
            json_encode($data)
        );

        $dataResult = json_decode($get, true);
        return $dataResult;
    }

    public function getDebtorPrices($debtorId)
    {
        $client = $this->_restCurlClient->getClient();
        $suc = $client->callAfas(
            'GET',
            'connectors/Profit_SalesPrice_Magento',
            [
                'filterfieldids' => 'Debtor',
                'filtervalues'   =>  $debtorId,
                'operatortypes' => 1,
                'take' => 1000

            ]
        );
        return json_decode($suc, true);
    }
    public function getDebtorAllRows()
    {
        $client = $this->_restCurlClient->getClient();
        $suc = $client->callAfas(
            'GET',
            'connectors/magento_debtor',
            [
                'take' => 1000

            ]
        );
        //$deb = json_decode($suc, true);
        //print_r($deb);
        return $suc;
    }

    public function getDebtorById($debtorId)
    {
        $client = $this->_restCurlClient->getClient();
        $suc = $client->callAfas(
            'GET',
            'connectors/magento_debtor',
            [
                'filterfieldids' => 'DebtorId',
                'filtervalues'   =>  $debtorId,
                'operatortypes' => 1,
                'take' => 1

            ]
        );
        $this->logger->debug($suc);
        $dataResult = json_decode($suc, true);
        return $dataResult['rows'][0];
    }

    public function getPackingSlip($afasOrderId, $lines, $debtorId){

        try {
            $client = $this->_restCurlClient->getClient();
            $data = [
                'FbDeliveryNote' => [
                    'Element' => [
                        'Fields' => [
                            'DbId' => $debtorId,
                            'SoOr' => $afasOrderId,
                            'RdDe' => true,
                            "U8125C7704DCB488E8F6925B06F29AEDC" => true,
                            "UF05B8560482CD8969E569CA1B4BE6A7F" => $lines['packing_time'],
                            "U5F64804145D65B21F54BAB99D490607C" => false,
                            "U88ECEF49463B0CC37B1872BC1F943881" => false,
                            "U1894E7EC43E51449CDEF17ABAA571DD5" => $lines['packing_trace'] ,
                            "U2A00775A4471FF87D4EA1E9826C973B0" => true
                        ],
                        'Objects' => [
                            'FbDeliveryNoteLines' => [
                                $lines['Objects']
                            ]
                        ]
                    ]

                ]
            ];
            $get = $client->callAfas(
                'POST',
                'connectors/FbDeliveryNote',
                [],
                json_encode($data)
            );

            $dataResult = json_decode($get, true);

            } catch (\RuntimeException $e){
                $dataResult['results']['FbDeliveryNote']['OrNu']=null;
            }
        return $dataResult;
    }

    public function getGUIDByAfasOrderId($afasOrderId){
        $client = $this->_restCurlClient->getClient();
        $get = $client->callAfas(
            'GET',
            'connectors/GUID_Salesorder',
            [
                'filterfieldids' => 'Ordernummer',
                'filtervalues'   =>  $afasOrderId,
                'operatortypes' => '1'

            ]
        );

        $dataResult = json_decode($get, true);
        return $dataResult;
    }

    public function getRekeningById($debtorId)
    {
        $client = $this->_restCurlClient->getClient();
        $suc = $client->callAfas(
            'GET',
            'connectors/Magento_Rekeningsaldi_debiteuren',
            [
                'filterfieldids' => 'Rekeningnummer,Administratie',
                'filtervalues'   =>  $debtorId,
                'operatortypes' => '1,1',
                'take' => 1

            ]
        );
        $this->logger->debug($suc);
        $dataResult = json_decode($suc, true);
        //$this->logger->debug('fdfddfgf'.$dataResult);
        if (!empty($dataResult['rows'])) {
            $this->logger->debug(print_r($dataResult, true));
            return $dataResult['rows'][0];
        } else {
            return false;
        }
    }

    private function setInfo($customer, $address)
    {
        $street = $address->getStreet();
        $Ad = '';
        $HmNr = '';
        $HmAd = '';
        if (isset($street[0])) {
            $Ad = $street[0];
        }
        if (isset($street[1])) {
            $HmNr = $street[1];
        }
        if (isset($street[2])) {
            $HmAd = $street[2];
        }

        $Fields = [
        'IsDb' => true,
        'VaId' => '',//$customer->getCustomAttribute('vat_duty')->getValue(),
        'PaCd' => "14",//$customer->getCustomAttribute('pay_con')->getValue(),
        'LgId' => 'NL', //$customer->getCustomAttribute('language')->getValue(),
        'CuId' => 'EUR',
        'VaDu' => 1,
        'ColA' => 1400,
        'CsDa' => $customer->getCreatedAt(),
        'DeCo' => 0,
        'InPv' => 'U',
        'OrPr' => 4,
        'UDA6A72CC46E5251C1F082594B02614C0' => true,
        'U59227BB0427C7D37B67FF3A1D6924866' => '05'
        ];

        $KnOrganisationFields = [
        'PadAdr' => true,
        'AutoNum' => true,
        'MatchOga' => 6,
        'Nm' => $address->getCompany(),
        'TeNr' => $address->getTelephone(),
        'EmAd' => $customer->getEmail(),
        'HoPa' => '',//$customer->getCustomAttribute('company_website')->getValue(),
        ];
        $KnOrganisationAdrFields = [
        'CoId' => $address->getCountryId(),
        'PbAd' => false,
        'Ad' => $Ad,
        'HmNr' => $HmNr,
        'HmAd' => $HmAd,
        'ZpCd' => $address->getPostcode(),
        'Rs' => $address->getCity(),
        'ResZip' => true
        ];
        $kn['KnSalesRelationOrg']['Element']['Fields'] = $Fields;
        $kn['KnSalesRelationOrg']['Element']['Objects']['KnOrganisation']['Element']['Fields'] = $KnOrganisationFields;
        $kn['KnSalesRelationOrg']['Element']['Objects']['KnOrganisation']['Element']['Objects']['KnBasicAddressAdr']['Element']['Fields'] = $KnOrganisationAdrFields;
        return $kn;
    }

    /*{
  "KnSalesRelationOrg": {
    "Element": {
      "Fields": {
        "IsDb": true,
        "VaId": "",
        "PaCd": "14",
        "LgId": "NL",
        "CuId": "EUR",
        "VaDu": 1,
        "ColA": "1400",
        "CsDa": "2021-06-16 14:46:05",
        "DeCo": 0,
        "InPv": "U",
        "OrPr": 3,
        "UDA6A72CC46E5251C1F082594B02614C0": true,
        "U59227BB0427C7D37B67FF3A1D6924866": "05"
      },
      "Objects": [
        {
          "KnOrganisation": {
            "Element": {
              "Fields": {
                "PadAdr": true,
                "AutoNum": true,
                "MatchOga": 6,
                "Nm": "Maxsel Test",
                "TeNr": "3423432434",
                "EmAd": "a@snzzzzffff.com",
                "HoPa": ""
              },
              "Objects": [
                {
                  "KnBasicAddressAdr": {
                    "Element": {
                      "Fields": {
                        "CoId": "NL",
                        "PbAd": false,
                        "Ad": "Roosveldstraat",
                        "HmNr": 42,
                        "HmAd": "A",
                        "ZpCd": "1421bl",
                        "Rs": "Uithoorn",
                        "AdAd": "A",
                        "BeginDate": "2021-06-17"
                      }
                    }
                  }
                }
              ]
            }
          }
        }
      ]
    }
  }
}

Result

{
  "KnSalesRelationOrg": {
    "DbId": "10272",
    "BcCo": "1000563",
    "BcId": "775"
  }
}

*/
}
