<?php

namespace BigBridge\ProductImport\Helper;
use PracticalAfas\Client\RestCurlClient;
/**
 * @author Patrick van Bergen
 */
class Config {
    
    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

   /**
   * Recipient email config path
   */
   const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
   /**
   * Recipient email config path
   */
   const XML_PATH_API_KEY = 'bigbridge/integration/api_key';

   public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
   {
      $this->scopeConfig = $scopeConfig;
   }

   /**
   * Sample function returning config value
   **/

  public function getAfasCustomerId() {
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

     return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);


  }

  public function getAfasToken() {
     $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

     return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, $storeScope);


  }

}
