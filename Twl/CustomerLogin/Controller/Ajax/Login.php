<?php

/**

 * Copyright © 2016 Magento. All rights reserved.

 * See COPYING.txt for license details.

 */



namespace Twl\CustomerLogin\Controller\Ajax;



use Magento\Customer\Api\AccountManagementInterface;

use Magento\Framework\Exception\EmailNotConfirmedException;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;

use Magento\Framework\App\ObjectManager;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\Exception\LocalizedException;



/**

 * Login controller

 *

 * @method \Magento\Framework\App\RequestInterface getRequest()

 * @method \Magento\Framework\App\Response\Http getResponse()

 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)

 */

class Login extends \Magento\Framework\App\Action\Action

{

    /**

     * @var \Magento\Framework\Session\Generic

     */

    protected $session;



    /**

     * @var AccountManagementInterface

     */

    protected $customerAccountManagement;



    /**

     * @var \Magento\Framework\Json\Helper\Data $helper

     */

    protected $helper;



    /**

     * @var \Magento\Framework\Controller\Result\JsonFactory

     */

    protected $resultJsonFactory;



    /**

     * @var \Magento\Framework\Controller\Result\RawFactory

     */

    protected $resultRawFactory;



    /**

     * @var AccountRedirect

     */

    protected $accountRedirect;



    /**

     * @var ScopeConfigInterface

     */

    protected $scopeConfig;
	
	
	protected $customerFactory;
	
	
	protected $customerCollection;



    /**

     * Initialize Login controller

     *

     * @param \Magento\Framework\App\Action\Context $context

     * @param \Magento\Customer\Model\Session $customerSession

     * @param \Magento\Framework\Json\Helper\Data $helper

     * @param AccountManagementInterface $customerAccountManagement

     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory

     */

    public function __construct(

        \Magento\Framework\App\Action\Context $context,

        \Magento\Customer\Model\Session $customerSession,

        \Magento\Framework\Json\Helper\Data $helper,

        AccountManagementInterface $customerAccountManagement,

        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		
		\Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,

        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory

    ) {

        parent::__construct($context);

        $this->customerSession = $customerSession;
		
		$this->customerFactory = $customerFactory;
		
		$this->customerCollection = $customerCollection;

        $this->helper = $helper;

        $this->customerAccountManagement = $customerAccountManagement;

        $this->resultJsonFactory = $resultJsonFactory;

        $this->resultRawFactory = $resultRawFactory;

    }



    /**

     * Get account redirect.

     * For release backward compatibility.

     *

     * @deprecated

     * @return AccountRedirect

     */

    protected function getAccountRedirect()

    {

        if (!is_object($this->accountRedirect)) {

            $this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);

        }

        return $this->accountRedirect;

    }



    /**

     * Account redirect setter for unit tests.

     *

     * @deprecated

     * @param AccountRedirect $value

     * @return void

     */

    public function setAccountRedirect($value)

    {

        $this->accountRedirect = $value;

    }



    /**

     * @deprecated

     * @return ScopeConfigInterface

     */

    protected function getScopeConfig()

    {

        if (!is_object($this->scopeConfig)) {

            $this->scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);

        }

        return $this->scopeConfig;

    }



    /**

     * @deprecated

     * @param ScopeConfigInterface $value

     * @return void

     */

    public function setScopeConfig($value)

    {

        $this->scopeConfig = $value;

    }



    /**

     * Login registered users and initiate a session.

     *

     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}

     *

     * @return \Magento\Framework\Controller\ResultInterface

     * @SuppressWarnings(PHPMD.CyclomaticComplexity)

     */

    public function execute()

    {

        $credentials = null;

        $httpBadRequestCode = 400;



        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */

        $resultRaw = $this->resultRawFactory->create();

        try {

            $credentials = [

                                'username' => $this->getRequest()->getPost('username'),

                                'password' => $this->getRequest()->getPost('password')

                            ];

        } catch (\Exception $e) {

            return $resultRaw->setHttpResponseCode($httpBadRequestCode);

        }

        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {

            return $resultRaw->setHttpResponseCode($httpBadRequestCode);

        }



        $response = [

            'errors' => false,

            'message' => __('Login successful.')

        ];

        $errorMsg = __('The mobile number is not registered with us.');

        try {

           
			$collection = $this->customerCollection->addAttributeToSelect('*')
						  	->addAttributeToFilter('contact_number','+96-'.$credentials['username'])
						  	->load();
			
			if($collection->getSize()){
				//echo 'test'; exit;
				$customerData = $collection->getData();
				
				$customerId = $customerData[0]['entity_id'];
				
				if($customerId){
					
					$this->customerSession->setOtpAuth('1004');
					$this->customerSession->setOtpAuthId($customerId);
					$response = [

						'errors' => false,

						'message' => __('Login successful.'),
						
						'customer_id'=>$customerId

					];
	
				}else{
				
                    $response = [

                    'errors' => true,

                    // 'message' => 'LocalizedException'.$e->getMessage()

                    'message' => $errorMsg

                    ];
                }
				
				
			}else{
				
				$response = [

                'errors' => true,

                // 'message' => 'LocalizedException'.$e->getMessage()

                'message' => $errorMsg

            	];
			}
		   

           

        } catch (LocalizedException $e) {

            $response = [

                'errors' => true,

                // 'message' => 'LocalizedException'.$e->getMessage()

                'message' => $errorMsg

            ];

        } catch (\Exception $e) {

            $response = [

                'errors' => true,

                // 'message' => .$e->getMessage()

                'message' => $errorMsg

            ];

        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);

    }

}

