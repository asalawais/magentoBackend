<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

//use BigBridge\ProductImport\System\ConfigInterface;

use PracticalAfas\Client\RestCurlClient;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class ProductPublisher
 */
class BrouwersPackingAfas
{

    private $restCurlClient;
    private $_orderModel;
    protected $logger;
    protected $_orderCollectionFactory;
    protected $timezone;
    protected $date;
    protected $_filesystem;
    protected $_file;

    /**
     * ProductPublisher constructor.
     *
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ProductRepositoryFactory $repositoryFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */


    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Sales\Model\Order $orderModel
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderModel = $orderModel;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->date = $date;
        $this->_file = $file;
        $this->_filesystem = $filesystem;


    }


    /**
     * Publish products
     *
     * @return void
     */
    public function execute()
    {
        //echo $this->getLand(strtoupper('Nederland'));
        $this->getClient();

    }

    private function getClient(){

        $client = new RestCurlClient( [ 'customerId' => 89626, 'appToken' => '3E89D236B4414A3A9C2DD07C5FAC7911ADE9DDB740E1682BAE4FE2A57A9158DF' ] );

        $this->logger->info('Brouwer: start');

            $get = $client->callAfas(
                'GET',
                'connectors/Pakbon_Brouwers_export',
                [
                    'take' => 1000,
                    'filterfieldids' => 'B2C,Exporteren_naar_Brouwer,Geexporteerd_naar_Brouwer',
                    'filtervalues' => 'true,true,false',
                    'operatortypes' => '1,1,1'
                ]
            );
            //echo $get;
            $dataResult = json_decode($get, true);
            //print_r($dataResult);
            $afasOrders = $dataResult['rows'];
        $this->logger->info(print_r($afasOrders, true));
            //print_r($afasOrders);
            //die;
            //$orderN = $this->getOrder($order->getRealOrderId());
            if(count($afasOrders) > 0) {
                $sortedOrders = [];
                foreach( $afasOrders as $afasOrder ){
                    //$postCity = explode(' ',$afasOrder['Postcode']);
                    $collect = 'N';
                    if($afasOrder['Collect_order']==true){
                        $collect = 'Y';
                    }
                    if($afasOrder['Comments']==null){
                        $afasOrder['Comments'] = 'none';
                    }
                    $sortedOrders[$afasOrder['ordernummer']]['Afleveradres'] = $afasOrder['Afleveradres'];
                    $sortedOrders[$afasOrder['ordernummer']]['klantnaam'] = $afasOrder['klantnaam'];
                    $sortedOrders[$afasOrder['ordernummer']]['straat'] = $afasOrder['straat'];
                    $sortedOrders[$afasOrder['ordernummer']]['postcode'] = $afasOrder['Postcode'];
                    $sortedOrders[$afasOrder['ordernummer']]['city'] = $afasOrder['Woonplaats'];
                    $sortedOrders[$afasOrder['ordernummer']]['land'] = $this->getLand(strtoupper($afasOrder['Land']));
                    $sortedOrders[$afasOrder['ordernummer']]['Gewenste_leverdatum'] = $afasOrder['Gewenste_leverdatum'];
                    $sortedOrders[$afasOrder['ordernummer']]['Opdrachtnummer_referentie'] = $afasOrder['Opdrachtnummer_referentie'];
                    $sortedOrders[$afasOrder['ordernummer']]['mag'] = $afasOrder['mag'];
                    $sortedOrders[$afasOrder['ordernummer']]['collect'] = $collect;
                    $sortedOrders[$afasOrder['ordernummer']]['remark'] = $afasOrder['Comments'];
                    $sortedOrders[$afasOrder['ordernummer']]['magazijn'] = $afasOrder['magazijn'];
                    $sortedOrders[$afasOrder['ordernummer']]['GUID'] = $afasOrder['GUID'];
                    //$sortedOrders[$afasOrder['ordernummer']]['Adres_toevoeging'] = $afasOrder['Adres_toevoeging'];
                    $sortedOrders[$afasOrder['ordernummer']]['Nummer_pakbon'] = $afasOrder['Nummer_pakbon'];


                    $sortedOrders[$afasOrder['ordernummer']]['lines'][] = array('Itemcode' => $afasOrder['Itemcode'],'aantal' => $afasOrder['aantal']);



                    //$sortedOrders[] = $afasOrder;
                }
                //print_r($sortedOrders);
                $this->setBrouwerXML($sortedOrders);
            }

            //$dataResult = '{"Exporteren_naar_Brouwer": true,"Doorgestuurd_op": "2021-02-24T15:37:00Z","Afleveradres": 1502,"klantnaam": "Bedden Online BV","straat": "Stuartweg 39","postcode": "4131 NH  VIANEN UT","land": null,"ordernummer": "60000764","Gewenste_leverdatum": "2021-02-10T00:00:00Z","Opdrachtnummer_referentie": "10023","mag": "PML","magazijn": "Perfect Meubel Logistics Eur","Itemcode": "8718247572397","aantal": 1,"GUID": "{7698F2AF-5258-449C-827A-FF49C7427CAB}"}';
        //return json_decode($dataResult, true);
    }


    public function setBrouwerLines($lines){
        $lineXML = '';
        foreach($lines as $line) {
            $lineXML .= '<LINE>
                            <ARTICLECODE>'.$line['Itemcode'].'</ARTICLECODE>
                            <ORDERED>'.$line['aantal'].'</ORDERED>
                        </LINE >';
            }
        return $lineXML;
    }

    public function setBrouwerXML($afasOrders)
    {

        foreach($afasOrders as $key => $afasOrder) {
            //print_r($afasOrder);
          $date = $this->timezone->date(new \DateTime($afasOrder['Gewenste_leverdatum']))->format('Ymd');
        $XML = '<?xml version="1.0" ?>
        <MAIN>
        <OUTBOUND>
        <HEADER>
        <ORDERTYPE>B2C</ORDERTYPE>
        <TIMESTAMP>'.$this->date->gmtDate('YmdHis').'</TIMESTAMP>
        <CLIENT>3041</CLIENT>
        <DELCODECL>'.$afasOrder['Afleveradres'].'</DELCODECL>
        <DELNAME>'.htmlspecialchars($afasOrder['klantnaam']).'</DELNAME>
        <DELADDRESS>'.$afasOrder['straat'].'</DELADDRESS>
        <DELZIP>'.$afasOrder['postcode'].'</DELZIP >
        <DELCITY>'.$afasOrder['city'].'</DELCITY>
        <COUNTRY>'.$afasOrder['land'].'</COUNTRY>
        <REFERENCE-1>'.$key.'</REFERENCE-1>
        <REFERENCE-2>'.$afasOrder['Opdrachtnummer_referentie'].'</REFERENCE-2>
        <DELDATE>'.$date.'</DELDATE>
        <COLLECT>'.$afasOrder['collect'].'</COLLECT>
        <REMARK>'.$afasOrder['remark'].'</REMARK>
        </HEADER>'
        . $this->setBrouwerLines($afasOrder['lines']) .
        '</OUTBOUND>
        </MAIN>';

         $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
         $file = fopen($var.'xmlorders/algorder.'.$key.".xml","w");
         $fileDev = fopen($var.'xmlordersdev/algorder.'.$key.".xml","w");
         //$file = fopen('var/xmlorders/algorder.'.$key.".xml","w");
         fwrite($file,$XML);
         fwrite($fileDev,$XML);
         fclose($file);
         fclose($fileDev);

         $dataFile = $var.'xmlorders/algorder.'.$key.".xml";
         $result = $this->uploadCurl($dataFile);
         if($result){
           $response = $this->updateDeliveryNote($afasOrder['Nummer_pakbon']);
           $afasOrder = $response->results->FbDeliveryNote->OrNu;
           if($afasOrder){
               $this->unlinkFile('algorder.'.$afasOrder.'.xml');
               //echo "success";
           }
         }

        }

        return $XML;

    }


    private function uploadCurl($dataFile){

        $sftpServer    = 'sftp01.vanboxtel.cloud';
        $sftpUsername  = 'sftp-mpstextiles';
        $sftpPassword  = '53vnxqC@K4?vAt';
        $sftpPort      = 22;
        $sftpRemoteDir = '/prod';
        //$sftpRemoteDir = '/accept'; //dev

        $ch = curl_init('sftp://' . $sftpServer . ':' . $sftpPort . $sftpRemoteDir . '/' . basename($dataFile));
        //print_r('sftp://' . $sftpServer . ':' . $sftpPort . $sftpRemoteDir . '/' . basename($dataFile));
        $fh = fopen($dataFile, 'r');
        try {

        if ($fh) {
            curl_setopt($ch, CURLOPT_USERPWD, $sftpUsername . ':' . $sftpPassword);
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
            curl_setopt($ch, CURLOPT_INFILE, $fh);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($dataFile));
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);

            $response = curl_exec($ch);

            $error = curl_error($ch);
             print_r($error);
            curl_close($ch);

            if ($response) {
                return true;
            } else {
                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);
                $error = "Verbose information:\n" . $verboseLog . "\n";
                $this->logger->critical($error);
            }
         }
        } catch (\Exception $e) {
         $this->logger->critical($e->getMessage());
    }
    }



    private function updateDeliveryNote($OrNu){

        $client = new RestCurlClient( [ 'customerId' => 89626, 'appToken' => '3E89D236B4414A3A9C2DD07C5FAC7911ADE9DDB740E1682BAE4FE2A57A9158DF' ] );

        $suc = $client->callAfas('PUT', 'connectors/FbDeliveryNote', [], '{
  "FbDeliveryNote": {
    "Element": {
      "Fields": {
        "OrNu": "'.$OrNu.'",
        "U8125C7704DCB488E8F6925B06F29AEDC": false,
        "U5F64804145D65B21F54BAB99D490607C": true
      }
    }
  }
}');
        return json_decode($suc);
    }


    private function unlinkFile($fileName){
        $var = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();

        if ($this->_file->isExists($var.'xmlorders/'. $fileName))  {
            $this->_file->deleteFile($var.'xmlorders/' . $fileName);
        }

    }


    public function getLand($countryName){
        $lands = [
            "ANDORRA" => "AD",
            "VERENIGDE ARABISCHE" => "AE",
            "AFGHANISTAN" => "AF",
            "ANTIGUA EN BARBUDA" => "AG",
            "ANGUILLA" => "AI",
            "ALBANIE" => "AL",
            "ARMENIE" => "AM",
            "NEDERLANDSE ANTILLEN" => "AN",
            "ANGOLA" => "AO",
            "ANTARCTICA" => "AQ",
            "ARGENTINIE" => "AR",
            "AMERIKAANS-SAMOA" => "AS",
            "OOSTENRIJK" => "AT",
            "AUSTRIA" => "AT",
            "AUSTRALIE" => "AU",
            "ARUBA" => "AW",
            "AZERBEIDZJAN" => "AZ",
            "BELGIë" => "BE",
            "BOSNIE-HERZEGOVINA" => "BA",
            "BARBADOS" => "BB",
            "BANGLADESH" => "BD",
            "BELGIE" => "BE",
            "BURKINA FASO" => "BF",
            "BULGARIJE" => "BG",
            "BAHREIN" => "BH",
            "BOEROENDI" => "BI",
            "BENIN" => "BJ",
            "BERMUDA" => "BM",
            "BRUNEI DARUSSALAM" => "BN",
            "BOLIVIA" => "BO",
            "BRAZILIE" => "BR",
            "BAHAMA'S" => "BS",
            "BHUTAN" => "BT",
            "BOUVETEILAND" => "BV",
            "BOTSWANA" => "BW",
            "BELARUS" => "BY",
            "BELIZE" => "BZ",
            "CANADA" => "CA",
            "COCOSEILANDEN" => "CC",
            "CENTRAAL-AFRIKAANSE" => "CF",
            "CONGO" => "CG",
            "ZWITSERLAND" => "CH",
            "IVOORKUST" => "CI",
            "COOKEILANDEN" => "CK",
            "CHILI" => "CL",
            "KAMEROEN" => "CM",
            "CHINA" => "CN",
            "COLOMBIA" => "CO",
            "COSTA RICA" => "CR",
            "CUBA" => "CU",
            "KAAPVERDIE" => "CV",
            "CHRISTMASEILAND" => "CX",
            "CYPRUS" => "CY",
            "TSJECHIE" => "CZ",
            "DUITSLAND" => "DE",
            "DIVERSEN" => "DI",
            "DIJBOUTI" => "DJ",
            "DENEMARKEN" => "DK",
            "DOMINICA" => "DM",
            "DOMINICAANSE REPUBLI" => "DO",
            "ALGERIJE" => "DZ",
            "ECUADOR" => "EC",
            "ESTLAND" => "EE",
            "EGYPTE" => "EG",
            "ERITREA" => "ER",
            "SPANJE" => "ES",
            "ETHIOPIE" => "ET",
            "FINLAND" => "FI",
            "FIJI" => "FJ",
            "FALKLANDEILANDEN" => "FK",
            "MICRONESIE" => "FM",
            "FAEROER" => "FO",
            "FRANKRIJK" => "FR",
            "FRANCE" => "FR",
            "GABON" => "GA",
            "VERENIGD KONINKRIJK" => "GB",
            "GRENADA" => "GD",
            "GEORGIE" => "GE",
            "GHANA" => "GH",
            "GIBRALTAR" => "GI",
            "GROENLAND" => "GL",
            "GAMBIA" => "GM",
            "GUINEE" => "GN",
            "EQUATORIAAL-GUINEA" => "GQ",
            "GRIEKENLAND" => "GR",
            "ZUID-GEORGIE EN DE Z" => "GS",
            "GUATEMALA" => "GT",
            "GUAM" => "GU",
            "GUINEE-BISSAU" => "GW",
            "GUYANA" => "GY",
            "HONGKONG" => "HK",
            "HEARD-EN MCDONALDEIL" => "HM",
            "HONDURAS" => "HN",
            "KROATIE" => "HR",
            "HAITI" => "HT",
            "HONGARIJE" => "HU",
            "INDONESIE" => "ID",
            "IERLAND" => "IE",
            "ISRAEL" => "IL",
            "INDIA" => "IN",
            "CHAGOSEILANDEN" => "IO",
            "IRAK" => "IQ",
            "IRAN" => "IR",
            "IJSLAND" => "IS",
            "ITALIE" => "IT",
            "JAIMAICA" => "JM",
            "JORDANIE" => "JO",
            "JAPAN" => "JP",
            "KENIA" => "KE",
            "KIRGIZIE" => "KG",
            "CAMBODJA" => "KH",
            "KIRIBATI" => "KI",
            "COMOREN" => "KM",
            "SAINT KITTS EN NEVIS" => "KN",
            "NOORD-KOREA" => "KP",
            "KAZACHSTAN" => "KS",
            "KOEWEIT" => "KW",
            "KAAIMANEILANDEN" => "KY",
            "ZUID-KOREA" => "KZ",
            "LAOS" => "LA",
            "LIBANON" => "LB",
            "SAINT LUCIA" => "LC",
            "LIECHTENSTEIN" => "LI",
            "SRI LANKA" => "LK",
            "LIBERIA" => "LR",
            "LESOTHO" => "LS",
            "LITOUWEN" => "LT",
            "LUXEMBURG" => "LU",
            "LETLAND" => "LV",
            "LIBIE" => "LY",
            "MAROKKO" => "MA",
            "MARTINIQUE" => "MAR",
            "MONACO" => "MC",
            "MOLDAVIE" => "MD",
            "MONTENEGRO" => "ME",
            "MADAGASKAR" => "MG",
            "MARSHALLEILANDEN" => "MH",
            "MACEDONIE" => "MK",
            "MALI" => "ML",
            "MYANMAR" => "MM",
            "MONGOLIE" => "MN",
            "MACAU" => "MO",
            "NOORDELIJKE MARIANEN" => "MP",
            "MAURITANIE" => "MR",
            "MONTSERRAT" => "MS",
            "MALTA" => "MT",
            "MAURITIUS" => "MU",
            "MALDIVEN" => "MV",
            "MALAWI" => "MW",
            "MEXICO" => "MX",
            "MALEISIE" => "MY",
            "MOZAMBIQUE" => "MZ",
            "NAMIBIE" => "NA",
            "NIEUW-CALEDONIE" => "NC",
            "NIGER" => "NE",
            "NORFOLK" => "NF",
            "NIGERIA" => "NG",
            "NICARAGUA" => "NI",
            "NOORD-IERLAND" => "NIR",
            "NEDERLAND" => "NL",
            "NOORWEGEN" => "NO",
            "NEPAL" => "NP",
            "NAURU" => "NR",
            "NIUE" => "NU",
            "NIEUW-ZEELAND" => "NZ",
            "OMAN" => "OM",
            "ONBEKEND" => "ON",
            "PANAMA" => "PA",
            "PERU" => "PE",
            "FRANS-POLYNESIE" => "PF",
            "PAPOEA-NIEUW-GUINEA" => "PG",
            "FILIPIJNEN" => "PH",
            "PAKISTAN" => "PK",
            "POLEN" => "PL",
            "SAINT-PIERRE EN MIQU" => "PM",
            "PITCARNEILANDEN" => "PN",
            "GEBIED ONDER PALESTI" => "PS",
            "PORTUGAL" => "PT",
            "PALAU" => "PW",
            "PARAGUAY" => "PY",
            "QATAR" => "QA",
            "REUNION" => "RE",
            "ROEMENIE" => "RO",
            "SERVIE" => "RS",
            "RUSLAND" => "RU",
            "RWANDA" => "RW",
            "SAOEDI-ARABIE" => "SA",
            "SALOMONSEILANDEN" => "SB",
            "SEYCHELLEN" => "SC",
            "SCHOTLAND" => "SCT",
            "SOEDAN" => "SD",
            "ZWEDEN" => "SE",
            "SINGAPORE" => "SG",
            "ST. HELENA" => "SH",
            "SLOVENIE" => "SI",
            "SLOWAKIJE" => "SK",
            "SIERRA LEONE" => "SL",
            "SAN MARINO" => "SM",
            "SENEGAL" => "SN",
            "SOMALIE" => "SO",
            "SURINAME" => "SR",
            "SAO TOME EN PRINCIPE" => "ST",
            "EL SALVADOR" => "SV",
            "SYRIE" => "SY",
            "SWAZILAND" => "SZ",
            "TURKS-EN CAICOSEILAN" => "TC",
            "TSJAAD" => "TD",
            "FRANDE ZUIDELIJKE GE" => "TF",
            "TOGO" => "TG",
            "THAILAND" => "TH",
            "TADZJIKISTAN" => "TJ",
            "TOKELAU-EILANDEN" => "TK",
            "TIMOR-LE" => "TL",
            "TURKMENISTAN" => "TM",
            "TUNESIE" => "TN", "TONGA" => "TO",
            "TURKIJE" => "TR",
            "TRINIDAD EN TOBAGO" => "TT",
            "TUVALU" => "TV",
            "TAIWAN" => "TW",
            "TANZANIA" => "TZ",
            "OEKRAINE" => "UA",
            "OEGANDA" => "UG",
            "UNITED KINGDOM" => "UK",
            "VERAFGELEGEN EILANDJ" => "UM",
            "VERENIGDE STATEN" => "US",
            "URUGUAY" => "UY",
            "OEZBEKISTAN" => "UZ",
            "VATICAAN" => "VA",
            "SAINT VINCENT EN DE" => "VC", "
            VENEZUELA" => "VE",
            "BRITSE MAAGDENEILAND" => "VG",
            "AMERIKAANDE MAAGDENE" => "VI",
            "VIETNAM" => "VN",
            "VANUATU" => "VU",
            "WALES" => "WA",
            "WALLIS EN FUTUNA" => "WF",
            "SAMOA" => "WS",
            "CEUTA" => "XC",
            "KOSOVO" => "XK",
            "MELILLA" => "XL",
            "JEMEN" => "YE",
            "MAYOTTE" => "YT",
            "ZUID-AFRIKA" => "ZA",
            "ZAMBIA" => "ZM",
            "ZIMBABWE" => "ZW"
        ];
        return $lands[$countryName];
        /*$arr = [];
        if (($handle = fopen("/home/mohammad/Downloads/lands.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $arr[] = '"'.strtolower($data[1]).'"=>"'.$data[0].'"';
            }
            fclose($handle);
        }
        $ar = implode(",", $arr);
        print_r($ar);
        */
    }
}
