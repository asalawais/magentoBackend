<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Jawal\Sms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
// use Magento\Backend\Model\Menu\Config;

class Data extends AbstractHelper
{
    const SMS_JAWAL_ENABLED = 'sms/jawal/enabled';
    // const SMS_JAWAL_USERNAME = 'sms/jawal/username';
    // const SMS_JAWAL_PASSWORD = 'sms/jawal/password';
    const SMS_JAWAL_OAUTH_TOKEN = 'sms/jawal/oauth_token';
    const SMS_JAWAL_SENDER_ID = 'sms/jawal/sender_id';
    const SMS_JAWAL_ORDER_NEW = 'sms/jawal/order_new_created_template';
    const SMS_JAWAL_ORDER_STATUS = 'sms/jawal/order_status_change_template';
    const SMS_JAWAL_ORDER_TRACK = 'sms/jawal/order_tracking_number_template';
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */

     /*   taqnyat account  */
     private $base;
     public $auth;
     public $result;
     private $method;
     private $json = array();
     public $error = "";
     /*   taqnyat account  */

    public function __construct(
      \Magento\Framework\App\Helper\Context $context,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
      $this->scopeConfig = $scopeConfig;
      parent::__construct($context);

    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
      return $this->scopeConfig->getValue(self::SMS_JAWAL_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getUsername()
    {
      return $this->scopeConfig->getValue(self::SMS_JAWAL_USERNAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getPassword()
    {
      return $this->scopeConfig->getValue(self::SMS_JAWAL_PASSWORD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getOauthToken()
    {
      return $this->scopeConfig->getValue(self::SMS_JAWAL_OAUTH_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getSenderId()
    {
      return $this->scopeConfig->getValue(self::SMS_JAWAL_SENDER_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getOrderTemplates($type)
    {
      $template = "";
      if ($type == "order_new") {
        $template = $this->scopeConfig->getValue(self::SMS_JAWAL_ORDER_NEW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      }else if ($type == "order_status") {
        $template = $this->scopeConfig->getValue(self::SMS_JAWAL_ORDER_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      }else if ($type == "order_tracking") {
        $template = $this->scopeConfig->getValue(self::SMS_JAWAL_ORDER_TRACK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      }
      return $template;
    }


    public function sendSms($type, $dataArr)
    {
      $this->auth = $this->getOauthToken();
      $this->method = 'curl';
      $this->base = 'https://api.taqnyat.sa';
      // $message = $this->balance();
      $Uri = $smsTemplate = $response = "";
      $mobileNumber = ""; //"";
      $recipients = [];
      if ($this->isEnabled()) {

        $smsTemplate = $this->getOrderTemplates($type);
        foreach($dataArr as $key=>$val){
          $smsTemplate = str_replace("[$key]", $val, $smsTemplate);
          if ($key == 'customer_telephone' && $val) {
            $mobileNumber = $val;
          }else if ($key == 'shipping_telephone' && $val) {
            $mobileNumber = $val;
          }else if ($key == 'billing_telephone' && $val) {
            $mobileNumber = $val;
          }
        }
        if ($mobileNumber) {
          $recipients = [$mobileNumber];
          $sender     = $this->getSenderId();;
          $response    = $this->sendMsg($smsTemplate, $recipients, $sender);
        }

        // $callSmsApiResponseArr = $this->callSmsApi($smsTemplate, $mobileNumber);
        // $Uri = $callSmsApiResponseArr['uri'];
        // $response = $callSmsApiResponseArr['response'];
      }

      $debugMsg = 'Ahmad '.$type.': ';
      $debugMsg .= 'taqnyat : '.$response.' '.json_encode($recipients).', sender:'. $sender;
      $debugMsg .= 'isEnabled: '.$this->isEnabled();
      $debugMsg .= 'getOauthToken: '.$this->getOauthToken();
      // $debugMsg .= 'getPassword: '.$this->getPassword();
      $debugMsg .= 'getSenderId: '.$this->getSenderId();
      $debugMsg .= 'smsTemplate: '.$smsTemplate;
      $debugMsg .= 'mobileNumber: '.$mobileNumber;
      $debugMsg .= 'Uri: '.$Uri;
      $debugMsg .= 'Response: '.$response;
      $debugMsg .= 'jsonArr: '.json_encode($dataArr);

      \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Psr\Log\LoggerInterface::class)->debug($debugMsg);

    }

    public function callSmsApi($smsTemplate, $mobileNumber)
    {
      // http://www.jawalsms.net/httpSmsProvider.aspx?username=firstshow&password=ali8520&mobile=ENTER_YOUR_MOBILE_NUMBER&unicode=E&message=This is from Ahmad for testing&sender=firstsho-AD
      //
      $oAuthToken = $this->getOauthToken();
      // $username = $this->getUsername();
      // $password = $this->getPassword();
      $originator = $this->getSenderId();
      $mobileNumber = $this->format_mobile_as_international($mobileNumber);
      $Ucode = $this->IsItUnicode($smsTemplate);
      if($Ucode == 'U')
      {
      	$smsTemplate = $this->ToUTF($smsTemplate);
      }
      $smsTemplate = urlencode($smsTemplate);
      $dataArr = array();
      if ($username && $password && $mobileNumber && $originator && $smsTemplate && $Ucode) {
          $GatewayURL= "http://www.jawalsms.net/httpSmsProvider.aspx";
          $url = $GatewayURL."?username=".$username."&password=".$password."&mobile=".$mobileNumber."&sender=".$originator."&message=".$smsTemplate."&unicode=".$Ucode;
          // $url = 'http://www.jawalsms.net/httpSmsProvider.aspx?username='.$username .'&password='.$password.'&mobile='.$mobileNumber.'&unicode=E&message='.$smsTemplate.'&sender=firstsho-AD';
          $response = $this->postDataCurl($url, $smsTemplate);
          $response = $this->GetData($url);
          @$response=(integer)str_replace(" ","",@$response);
          $finalResult = $response;

          $dataArr['uri'] = $url;
          $dataArr['finalResult'] = $finalResult;
          $dataArr['response'] = $response;
      }
      return $dataArr;
    }

    function GetData($url){
      if(!$url || $url==""){
        return "No URL";
      }else{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLE_HTTP_NOT_FOUND,1);
        //curl_setopt($ch, CURLOPT_FAILONERROR,1);
        $LastData = curl_exec ($ch);
        curl_close ($ch);
        return $LastData;
      }
    }

    public function format_mobile_as_international( $number, $args=null ) {
    	$defaults = array(
    		'Country Code' => '966', // (966) 555555555
    		'Local Prefix' => '0',   // (0) 555555555
    		'First Digit' => '5', // 0 (5) 55555555
    		'IDD' => '00', // (00) 966 555555555
    		'Local Length' => 10 // length as dialled locally e.g. 0555555555 = 10 digits
    	);
    	$args = array_merge( $defaults, (array) $args );

    	// only keep digits: no spaces, dashes, plus signs, etc.
    	$number = preg_replace( "/[^0-9]/", "", $number );

    	// Phone number is like 0555555555
    	if ( $number[0] == $args['Local Prefix'] && strlen( $number ) == (int) $args['Local Length'] ) {
    		$number = substr( $number, strlen( $args['Local Prefix'] ) );
    		$result = $args['Country Code'] . $number;
    	}
    	// Phone number is like 555555555
    	elseif ( $number[0] == $args['First Digit'] && strlen( $number ) == (int) $args['Local Length'] - strlen( $args['First Digit'] ) ) {
    		$result = $args['Country Code'] . $number;
    	}
    	// Phone number is like 00966555555555
    	elseif ( substr( $number, 0, strlen( $args['IDD'] ) ) == $args['IDD'] && strlen( $number ) == (int) $args['Local Length'] - strlen( $args['Local Prefix'] ) + strlen( $args['IDD'] ) + strlen( $args['Country Code'] ) ) {
    		$result = substr( $number, strlen( $args['IDD'] ) );
    	}
    	// Phone number is like 966555555555
    	elseif ( substr( $number, 0, 3 ) == $args['Country Code'] && strlen( $number ) == (int) $args['Local Length'] - strlen( $args['Local Prefix'] ) + strlen( $args['Country Code'] ) ) {
    		$result = $number;
    	}
    	// else omit

    	return $result;
    }

    public function postDataCurl($url, $data, $fields=array()){
  		$ch		= curl_init();
  		curl_setopt($ch, CURLOPT_URL, $url);
  		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      if ($fields) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      }
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'User-Agent: PHP SOAP-NTLM Client',
      // This is always utf-8, does not follow $this->options['encoding']:
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($data),
    ) );

  		$result = curl_exec($ch);
  		if($result === false){
  			//die('Curl failed ' . curl_error());
  		}
  		curl_close($ch);
  		// echo $result;exit;
  		return $result;
  	}

    public function IsItUnicode($msg){
      $unicode = 'E';
      $str = "ÏÌÍÎåÚÛÝÞËÕÖØßãäÊÇáÈíÓÔÙÒæÉìáÇÑÄÁÆÅáÅÃáÃÂáÂ";
      for($i=0;$i<=strlen($str);$i++)
      {
        $strResult= substr($str,$i,1);
        for($R=0;$R<=strlen($msg);$R++)
        {
          $msgResult= substr($msg,$R,1);
          if($strResult==$msgResult && $strResult)
          $unicode = 'U';
        }
      }

      return $unicode;
    }

    function ToUTF($message)
    {
    	$chrArray[0]="¡";
    	$unicodeArray[0]="060C";
    	$chrArray[1]="º";
    	$unicodeArray[1]="061B";
    	$chrArray[2]="¿";
    	$unicodeArray[2]="061F";
    	$chrArray[3]="Á";
    	$unicodeArray[3]="0621";
    	$chrArray[4]="Â";
    	$unicodeArray[4]="0622";
    	$chrArray[5]="Ã";
    	$unicodeArray[5]="0623";
    	$chrArray[6]="Ä";
    	$unicodeArray[6]="0624";
    	$chrArray[7]="Å";
    	$unicodeArray[7]="0625";
    	$chrArray[8]="Æ";
    	$unicodeArray[8]="0626";
    	$chrArray[9]="Ç";
    	$unicodeArray[9]="0627";
    	$chrArray[10]="È";
    	$unicodeArray[10]="0628";
    	$chrArray[11]="É";
    	$unicodeArray[11]="0629";
    	$chrArray[12]="Ê";
    	$unicodeArray[12]="062A";
    	$chrArray[13]="Ë";
    	$unicodeArray[13]="062B";
    	$chrArray[14]="Ì";
    	$unicodeArray[14]="062C";
    	$chrArray[15]="Í";
    	$unicodeArray[15]="062D";
    	$chrArray[16]="Î";
    	$unicodeArray[16]="062E";
    	$chrArray[17]="Ï";
    	$unicodeArray[17]="062F";
    	$chrArray[18]="Ð";
    	$unicodeArray[18]="0630";
    	$chrArray[19]="Ñ";
    	$unicodeArray[19]="0631";
    	$chrArray[20]="Ò";
    	$unicodeArray[20]="0632";
    	$chrArray[21]="Ó";
    	$unicodeArray[21]="0633";
    	$chrArray[22]="Ô";
    	$unicodeArray[22]="0634";
    	$chrArray[23]="Õ";
    	$unicodeArray[23]="0635";
    	$chrArray[24]="Ö";
    	$unicodeArray[24]="0636";
    	$chrArray[25]="Ø";
    	$unicodeArray[25]="0637";
    	$chrArray[26]="Ù";
    	$unicodeArray[26]="0638";
    	$chrArray[27]="Ú";
    	$unicodeArray[27]="0639";
    	$chrArray[28]="Û";
    	$unicodeArray[28]="063A";
    	$chrArray[29]="Ý";
    	$unicodeArray[29]="0641";
    	$chrArray[30]="Þ";
    	$unicodeArray[30]="0642";
    	$chrArray[31]="ß";
    	$unicodeArray[31]="0643";
    	$chrArray[32]="á";
    	$unicodeArray[32]="0644";
    	$chrArray[33]="ã";
    	$unicodeArray[33]="0645";
    	$chrArray[34]="ä";
    	$unicodeArray[34]="0646";
    	$chrArray[35]="å";
    	$unicodeArray[35]="0647";
    	$chrArray[36]="æ";
    	$unicodeArray[36]="0648";
    	$chrArray[37]="ì";
    	$unicodeArray[37]="0649";
    	$chrArray[38]="í";
    	$unicodeArray[38]="064A";
    	$chrArray[39]="Ü";
    	$unicodeArray[39]="0640";
    	$chrArray[40]="ð";
    	$unicodeArray[40]="064B";
    	$chrArray[41]="ñ";
    	$unicodeArray[41]="064C";
    	$chrArray[42]="ò";
    	$unicodeArray[42]="064D";
    	$chrArray[43]="ó";
    	$unicodeArray[43]="064E";
    	$chrArray[44]="õ";
    	$unicodeArray[44]="064F";
    	$chrArray[45]="ö";
    	$unicodeArray[45]="0650";
    	$chrArray[46]="ø";
    	$unicodeArray[46]="0651";
    	$chrArray[47]="ú";
    	$unicodeArray[47]="0652";
    	$chrArray[48]="!";
    	$unicodeArray[48]="0021";
    	$chrArray[49]='"';
    	$unicodeArray[49]="0022";
    	$chrArray[50]="#";
    	$unicodeArray[50]="0023";
    	$chrArray[51]="$";
    	$unicodeArray[51]="0024";
    	$chrArray[52]="%";
    	$unicodeArray[52]="0025";
    	$chrArray[53]="&";
    	$unicodeArray[53]="0026";
    	$chrArray[54]="'";
    	$unicodeArray[54]="0027";
    	$chrArray[55]="(";
    	$unicodeArray[55]="0028";
    	$chrArray[56]=")";
    	$unicodeArray[56]="0029";
    	$chrArray[57]="*";
    	$unicodeArray[57]="002A";
    	$chrArray[58]="+";
    	$unicodeArray[58]="002B";
    	$chrArray[59]=",";
    	$unicodeArray[59]="002C";
    	$chrArray[60]="-";
    	$unicodeArray[60]="002D";
    	$chrArray[61]=".";
    	$unicodeArray[61]="002E";
    	$chrArray[62]="/";
    	$unicodeArray[62]="002F";
    	$chrArray[63]="0";
    	$unicodeArray[63]="0030";
    	$chrArray[64]="1";
    	$unicodeArray[64]="0031";
    	$chrArray[65]="2";
    	$unicodeArray[65]="0032";
    	$chrArray[66]="3";
    	$unicodeArray[66]="0033";
    	$chrArray[67]="4";
    	$unicodeArray[67]="0034";
    	$chrArray[68]="5";
    	$unicodeArray[68]="0035";
    	$chrArray[69]="6";
    	$unicodeArray[69]="0036";
    	$chrArray[70]="7";
    	$unicodeArray[70]="0037";
    	$chrArray[71]="8";
    	$unicodeArray[71]="0038";
    	$chrArray[72]="9";
    	$unicodeArray[72]="0039";
    	$chrArray[73]=":";
    	$unicodeArray[73]="003A";
    	$chrArray[74]=";";
    	$unicodeArray[74]="003B";
    	$chrArray[75]="<";
    	$unicodeArray[75]="003C";
    	$chrArray[76]="=";
    	$unicodeArray[76]="003D";
    	$chrArray[77]=">";
    	$unicodeArray[77]="003E";
    	$chrArray[78]="?";
    	$unicodeArray[78]="003F";
    	$chrArray[79]="@";
    	$unicodeArray[79]="0040";
    	$chrArray[80]="A";
    	$unicodeArray[80]="0041";
    	$chrArray[81]="B";
    	$unicodeArray[81]="0042";
    	$chrArray[82]="C";
    	$unicodeArray[82]="0043";
    	$chrArray[83]="D";
    	$unicodeArray[83]="0044";
    	$chrArray[84]="E";
    	$unicodeArray[84]="0045";
    	$chrArray[85]="F";
    	$unicodeArray[85]="0046";
    	$chrArray[86]="G";
    	$unicodeArray[86]="0047";
    	$chrArray[87]="H";
    	$unicodeArray[87]="0048";
    	$chrArray[88]="I";
    	$unicodeArray[88]="0049";
    	$chrArray[89]="J";
    	$unicodeArray[89]="004A";
    	$chrArray[90]="K";
    	$unicodeArray[90]="004B";
    	$chrArray[91]="L";
    	$unicodeArray[91]="004C";
    	$chrArray[92]="M";
    	$unicodeArray[92]="004D";
    	$chrArray[93]="N";
    	$unicodeArray[93]="004E";
    	$chrArray[94]="O";
    	$unicodeArray[94]="004F";
    	$chrArray[95]="P";
    	$unicodeArray[95]="0050";
    	$chrArray[96]="Q";
    	$unicodeArray[96]="0051";
    	$chrArray[97]="R";
    	$unicodeArray[97]="0052";
    	$chrArray[98]="S";
    	$unicodeArray[98]="0053";
    	$chrArray[99]="T";
    	$unicodeArray[99]="0054";
    	$chrArray[100]="U";
    	$unicodeArray[100]="0055";
    	$chrArray[101]="V";
    	$unicodeArray[101]="0056";
    	$chrArray[102]="W";
    	$unicodeArray[102]="0057";
    	$chrArray[103]="X";
    	$unicodeArray[103]="0058";
    	$chrArray[104]="Y";
    	$unicodeArray[104]="0059";
    	$chrArray[105]="Z";
    	$unicodeArray[105]="005A";
    	$chrArray[106]="[";
    	$unicodeArray[106]="005B";
    	$char="\ ";
    	$chrArray[107]=trim($char);
    	$unicodeArray[107]="005C";
    	$chrArray[108]="]";
    	$unicodeArray[108]="005D";
    	$chrArray[109]="^";
    	$unicodeArray[109]="005E";
    	$chrArray[110]="_";
    	$unicodeArray[110]="005F";
    	$chrArray[111]="`";
    	$unicodeArray[111]="0060";
    	$chrArray[112]="a";
    	$unicodeArray[112]="0061";
    	$chrArray[113]="b";
    	$unicodeArray[113]="0062";
    	$chrArray[114]="c";
    	$unicodeArray[114]="0063";
    	$chrArray[115]="d";
    	$unicodeArray[115]="0064";
    	$chrArray[116]="e";
    	$unicodeArray[116]="0065";
    	$chrArray[117]="f";
    	$unicodeArray[117]="0066";
    	$chrArray[118]="g";
    	$unicodeArray[118]="0067";
    	$chrArray[119]="h";
    	$unicodeArray[119]="0068";
    	$chrArray[120]="i";
    	$unicodeArray[120]="0069";
    	$chrArray[121]="j";
    	$unicodeArray[121]="006A";
    	$chrArray[122]="k";
    	$unicodeArray[122]="006B";
    	$chrArray[123]="l";
    	$unicodeArray[123]="006C";
    	$chrArray[124]="m";
    	$unicodeArray[124]="006D";
    	$chrArray[125]="n";
    	$unicodeArray[125]="006E";
    	$chrArray[126]="o";
    	$unicodeArray[126]="006F";
    	$chrArray[127]="p";
    	$unicodeArray[127]="0070";
    	$chrArray[128]="q";
    	$unicodeArray[128]="0071";
    	$chrArray[129]="r";
    	$unicodeArray[129]="0072";
    	$chrArray[130]="s";
    	$unicodeArray[130]="0073";
    	$chrArray[131]="t";
    	$unicodeArray[131]="0074";
    	$chrArray[132]="u";
    	$unicodeArray[132]="0075";
    	$chrArray[133]="v";
    	$unicodeArray[133]="0076";
    	$chrArray[134]="w";
    	$unicodeArray[134]="0077";
    	$chrArray[135]="x";
    	$unicodeArray[135]="0078";
    	$chrArray[136]="y";
    	$unicodeArray[136]="0079";
    	$chrArray[137]="z";
    	$unicodeArray[137]="007A";
    	$chrArray[138]="{";
    	$unicodeArray[138]="007B";
    	$chrArray[139]="|";
    	$unicodeArray[139]="007C";
    	$chrArray[140]="}";
    	$unicodeArray[140]="007D";
    	$chrArray[141]="~";
    	$unicodeArray[141]="007E";
    	$chrArray[142]="©";
    	$unicodeArray[142]="00A9";
    	$chrArray[143]="®";
    	$unicodeArray[143]="00AE";
    	$chrArray[144]="÷";
    	$unicodeArray[144]="00F7";
    	$chrArray[145]="×";
    	$unicodeArray[145]="00F7";
    	$chrArray[146]="§";
    	$unicodeArray[146]="00A7";
    	$chrArray[147]=" ";
    	$unicodeArray[147]="0020";
    	$chrArray[148]="\n";
    	$unicodeArray[148]="000D";
    	$chrArray[149]="\r";
    	$unicodeArray[149]="000A";
    	$chrArray[150]="\t";
    	$unicodeArray[150]="0009";
    	$chrArray[151]="é";
    	$unicodeArray[151]="00E9";
    	$chrArray[152]="ç";
    	$unicodeArray[152]="00E7";
    	$chrArray[153]="à";
    	$unicodeArray[153]="00E0";
    	$chrArray[154]="ù";
    	$unicodeArray[154]="00F9";
    	$chrArray[155]="µ";
    	$unicodeArray[155]="00B5";
    	$chrArray[156]="è";
    	$unicodeArray[156]="00E8";


    	$strResult="";

    	for($i=0;$i<strlen($message);$i++)
    	{
      	for($c=0;$c<count($chrArray);$c++)
      	{
        	if($chrArray[$c]==substr($message,$i,1))
        	{
          	substr($message,$i,1);
          	$strResult .=$unicodeArray[$c];
          	//   echo "[".$unicodeArray[$c]."]<br>";
        	}
      	}
    	}
    	return $strResult;
    }


    /***************** taqnyat account Methods**********************/

    /**
     * Check if user information is it API or mobile and password And if
     * this information is not empty set in variables for all api function other return error
     *
     * @param string $auth The Authentication from  taqnyat account
     * @return string $this->error If there is no error, it doesn't return anything
     **/
    public function setInfo($auth=NULL) {
		if(empty($auth)) {
			$this->error = 'Please Insert Authentication';
		} elseif (!empty($auth)) {
			$this->auth = $auth;
		}
		return $this->error;
    }

    /**
     * Check if user information is not empty and
     * prepare information in array to Merge with another message data
     * you can call this function just in api function because it's private
     *
     **/
    private function checkUserInfo() {
		$this->json = array();
		$this->error = "";
        if (!empty($this->auth)) {
            $this->json=array("auth"=>$this->auth);
        } else {
            $this->error = 'Add Authentication';
        }
    }

    /**
     * Using  send method you'r selected in api function and
     * if doesn't match with any cases return error
     *
     * @param string $data Message data
     * @return string $this->error If any error found
     **/
    private function run($host,$path,$data='',$reqestType="POST") {
        switch ($this->method) {
            case 'curl':
                $ch = curl_init();
                $header = array('content-type: application/json', 'Authorization: Bearer '.$this->auth);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_URL, $host.$path);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch,  CURLOPT_CUSTOMREQUEST, $reqestType);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $this->result = curl_exec($ch);
                break;
            case 'fsockopen':
				$host=str_replace('https://','',$host);
                $host=str_replace('http://','',$host);
                $length = strlen($data);
				$fsockParameter = "$reqestType $path HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.$host \r\n";
				$fsockParameter .= "Authorization: Bearer ".$this->auth."\r\n";
                $fsockParameter.= "Content-type: application/json \r\n";
                $fsockParameter.= "Content-length: $length \r\n\r\n";
                $fsockParameter .= "$data";
                $fsockConn = fsockopen('ssl://'. "www.$host", 443, $errno, $errstr, 30);
                fputs($fsockConn,$fsockParameter);
                $result = "";
                $clearResult = false;
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                    $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }
                break;
            case 'fopen':
                $contextOptions['http'] = array( 'method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $handle = fopen($host.$path, 'r', false, $contextResouce);
                $this->result = stream_get_contents($handle);
                break;
            case 'file':
                $contextOptions['http'] = array('method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $arrayResult = file($host.$path, FILE_IGNORE_NEW_LINES, $contextResouce);
                $this->result = $arrayResult[0];
                break;
            case 'file_get_contents':
                $contextOptions['http'] = array('method' => $reqestType, 'header'=>'Authorization: Bearer '.$this->auth."\r\n".'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $this->result = file_get_contents($host.$path, false, $contextResouce);
                break;
            default:
                $this->error = 'active one of the following portals (curl,fopen,fsockopen,file,file_get_contents) on server';
                return $this->error;
        }
        return $this->result;
    }


    /**
     * Send  message directly without separate message data
     * you can use to call function (sendMsg Or sendMsgWK).
     *
     * @param string $functionName Name of the function (required)
     * @param string $data Message data (required)
     * @return string $this->error If any error found
     **/
    public function callAPI ($functionName, $data,$port=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($port);
        if(empty($this->error)) {
            $this->json=array_merge($this->json,$data);
            $this->json['recipients']=explode(',',$this->json['recipients']);
            $this->json['lang']='3';
            $this->json=json_encode($this->json);
            switch ($functionName) {
                case 'sendMsg':
                        return $this->run('https://api.taqnyat.sa', '/v1/messages', $this->json);
                    break;
                default:
                    $this->error[] = 'method name not found You can select either (sendMsg).';
                    return $this->error;
            }
        }else{
            return $this->error;
        }
    }

    /**
     * Check if send method selected in function and
     * test send method if work or if method doesn't selected
     * test method  and choose which works
     *
     * @param string $method Send method
     * @return string $this->error If not empty method
     **/
	private function getSendMethod($method=NULL) {
		//Change Deafult Method
		if(!empty($method)){
			$this->method = strtolower($method);
		}
		//Check CURL
		if($this->method == 'curl') {
			if(function_exists("curl_init") && function_exists("curl_setopt") && function_exists("curl_exec") && function_exists("curl_close") && function_exists("curl_errno")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'CURL is not supported';
				} else {
					$this->method = 'fsockopen';
				}
			}
		}
		//Check fSockOpen
		if($this->method == 'fsockopen') {
			if(function_exists("fsockopen") && function_exists("fputs") && function_exists("feof") && function_exists("fread") && function_exists("fclose")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fSockOpen is not supported';
				} else {
					$this->method = 'fopen';
				}
			}
		}
		//Check fOpen
		if($this->method == 'fopen') {
			if(function_exists("fopen") && function_exists("fclose") && function_exists("fread")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fOpen is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}
		}
		//Check File
		if($this->method == 'file') {
			if(function_exists("file") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'File is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}
		}
		//Check file_get_contents
		if($this->method == 'file_get_contents') {
			if(function_exists("file_get_contents") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'file_get_contents is not supported';
				} else {
					$this->method=NULL;
				}
			}
		}
    }

    /**
     * Send message
     *
     * @param string $body (required)
     * @param string $recipients Numbers to send (between each number comma ",")(required)
     * @param string $sender Name of message sender (required)
     * @param integer $scheduled Date to send message like this 6/30/2017 17:30:00
     * @param string $method Send method
     * @return string $this->error If any error found
     */
    public function sendMsg($body, $recipients=array(), $sender,$scheduled='',$deleteId='',$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data = array(
		        'recipients'=>$recipients,
                	'sender'=>$sender,
                	'body'=>$body,
                	'scheduledDatetime'=>$scheduled,
		    	'deleteId'=>$deleteId,
            );
            $this->json =  $data;
            $this->json = json_encode($this->json);

            return $this->run($this->base,'/v1/messages',$this->json);
        }
        return $this->error;
    }

    /**
     * Get send status
     *
     * @param string $method Send method
     * @return string $this->result
     **/
    public function sendStatus($method=NULL) {
        $this->getSendMethod($method);
        $data=array(

        );
        $this->json=array_merge($this->json,$data);
        $this->json=json_encode($this->json);
        return $this->run($this->base,'/system/status',$this->json,"GET");
    }

    /**
     * Get balance of taqnyat account
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function balance($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/account/balance',$this->json,"GET");
        }
        return $this->error;
    }

	/**
     * Get senders of taqnyat account
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/

	public function senders($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/v1/messages/senders',$this->json,"GET");
        }
        return $this->error;
    }

    /**
     * Delete message Using message deleteKey
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function deleteMsg($deleteKey,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array('');
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run($this->base,'/v1/messages',$this->json,"DELETE");
        }
        return $this->error;
    }
}
