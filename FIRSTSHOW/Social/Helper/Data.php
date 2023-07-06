<?php

namespace FIRSTSHOW\Social\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;

class Data extends AbstractHelper
{
    protected $storeManager;
    protected $customer;
    protected $customerSession;
    protected $customerFactory;
	protected $_scopeConfig;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Customer $customer,
        Session $customerSession,
        CustomerFactory $customerFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
		$this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    public function getConfig($key, $store = null)
    {
        if ($store == null || $store == '') {
            $store = $this->storeManager->getStore()->getId();
        }
        $store = $this->storeManager->getStore($store);
        $config = $this->scopeConfig->getValue(
            'social/' . $key,
            ScopeInterface::SCOPE_STORE,
            $store);
        return $config;
    }

    public function checkLoggedIn()
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get('Magento\Customer\Model\Session')->isLoggedIn();
    }

    public function getUrl($value)
    {
        return $this->_getUrl($value, ['_secure' => true]);
    }

    public function getCustomersByFacebookId($fid, $websiteId)
    {
        $objectManager = ObjectManager::getInstance();
        $collection = $this->customer->getCollection()
            ->addAttributeToFilter('firstshow_social_fid', ['eq' => $fid])
            ->setPageSize(1);
        if ($this->scopeConfig->getValue('customer/account_share/scope', ScopeInterface::SCOPE_STORE, $this->storeManager->getStore($this->storeManager->getStore()->getId()))) {
            $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        if ($objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id', ['neq' => $this->customerSession->getCustomerId()]
            );
        }
        return $collection;
    }

    public function connectByFacebookId(Customer $customer, $fid, $ftoken)
    {
        $customer->setFirstshowSocialFid($fid)
            ->setFirstshowSocialFtoken($ftoken)
            ->save();
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }

    public function loginByCustomer(Customer $customer)
    {
        if ($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }

    public function getCustomersByEmail($email, $websiteId)
    {
        $objectManager = ObjectManager::getInstance();
        $collection = $this->customer->getCollection()
            ->addFieldToFilter('email', ['eq' => $email])
            ->setPageSize(1);
        if ($this->scopeConfig->getValue('customer/account_share/scope', ScopeInterface::SCOPE_STORE, $this->storeManager->getStore($this->storeManager->getStore()->getId()))) {
            $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        if ($objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id', ['neq' => $this->customerSession->getCustomerId()]
            );
        }
        return $collection;
    }

    public function createAccountAndLogin($email, $firstName, $lastName, $id, $token, $type, $websiteId)
    {
        $objectManager = ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer');
        $customer->setWebsiteId($websiteId)
            ->setId(null)
            ->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastName);
        if ($type == 'facebook') {
            $customer->setFirstshowSocialFid($id)
                ->setFirstshowSocialFtoken($token);
        }
        if ($type == 'google') {
            $customer->setFirstshowSocialGid($id)
                ->setFirstshowSocialGtoken($token);
        }
        if ($type == 'twitter') {
            $customer->setFirstshowSocialTid($id)
                ->setFirstshowSocialTtoken($token);
        }
        $customer->setPassword(md5($id));
        $customer->setConfirmation(null);
        $customer->isObjectNew(true);
        $customer->save();
        $customer->sendNewAccountEmail('confirmed', '', $this->storeManager->getStore()->getId());
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }

    public function getCustomersByTwitterId($tid, $websiteId)
    {
        $objectManager = ObjectManager::getInstance();
        $collection = $this->customer->getCollection()
            ->addAttributeToFilter('firstshow_social_tid', ['eq' => $tid])
            ->setPageSize(1);
        if ($this->scopeConfig->getValue('customer/account_share/scope', ScopeInterface::SCOPE_STORE, $this->storeManager->getStore($this->storeManager->getStore()->getId()))) {
            $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        if ($objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id', ['neq' => $this->customerSession->getCustomerId()]
            );
        }
        return $collection;
    }

    public function connectByTwitterId(Customer $customer, $tid, $ttoken)
    {
        $customer->setFirstshowSocialTid($tid)
            ->setFirstshowSocialTtoken($ttoken)
            ->save();
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }

    public function getCustomersByGoogleId($gid, $websiteId)
    {
        $objectManager = ObjectManager::getInstance();
        $collection = $this->customer->getCollection()
            ->addAttributeToFilter('firstshow_social_gid', ['eq' => $gid])
            ->setPageSize(1);
        if ($this->scopeConfig->getValue('customer/account_share/scope', ScopeInterface::SCOPE_STORE, $this->storeManager->getStore($this->storeManager->getStore()->getId()))) {
            $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        }
        if ($objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id', ['neq' => $this->customerSession->getCustomerId()]
            );
        }
        return $collection;
    }

    public function connectByGoogleId(Customer $customer, $gid, $gtoken)
    {
        $customer->setFirstshowSocialGid($gid)
            ->setFirstshowSocialGtoken($gtoken)
            ->save();
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }
	
	public function getStoreConfig($node){
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getEnable(){
		if($this->getStoreConfig('social/instagram_setting/enable_instagram')) {
			return true;
		}
		return false;
	}
	public function getAccessToken(){
		return $this->getStoreConfig('social/instagram_setting/access_token'); 
	}
	public function getNumber(){
		return $this->getStoreConfig('social/instagram_setting/number');
	} 
	public function getWidth(){
		if($this->getStoreConfig('social/instagram_setting/width')) {
			return $this->getStoreConfig('social/instagram_setting/width');
		}
		return '100';
	}
	public function getHeight(){
		if($this->getStoreConfig('social/instagram_setting/height')) {
			return $this->getStoreConfig('social/instagram_setting/height');
		}
		return '100';
	}

}