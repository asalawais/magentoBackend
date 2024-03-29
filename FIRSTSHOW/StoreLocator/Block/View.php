<?php

namespace FIRSTSHOW\StoreLocator\Block;

use FIRSTSHOW\StoreLocator\Model\StoreFactory;
use Magento\Framework\Session\SessionManager as SessionManager;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_countryFactory;
    protected $_storeFactory;
    protected $_filterProvider;
    protected $_coreRegistry;
    protected $_sessionManager;
    protected $_storeCollection = null;
    protected $_perPage = 10;
     protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        StoreFactory $storeFactory,
        SessionManager $sessionManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_countryFactory = $countryFactory;
        $this->_storeFactory = $storeFactory;
        $this->_filterProvider = $filterProvider;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        
        $id = $this->getRequest()->getParam('id');
        $store = $this->_objectManager->create('FIRSTSHOW\StoreLocator\Model\Store')->load($id);
		//$store = $this->getStoreView();                
		$title = $store->getName();
		$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbsBlock->addCrumb(
			'home',
			[
				'label' => __('Home'),
				'title' => __('Go to Home Page'),
				'link' => $this->_storeManager->getStore()->getBaseUrl()
			]
		);
		
		$breadcrumbsBlock->addCrumb('store_view', ['label' => $title, 'title' => $title]);
		
        
        return parent::_prepareLayout();
    }
    
    
    public function getStoreView() {
        $model = $this->_coreRegistry->registry('store_view');
        if($model->getImage()) {
            $model->setImageUrl($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$model->getImage());
        }
        return $model;
    }

    public function getDescription() {
        $storeId = $this->_storeManager->getStore()->getId();
        $store = $this->getStoreView();
        return $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($store->getDescription());
    }

    public function getTradingHours() {
        $storeId = $this->_storeManager->getStore()->getId();
        $store = $this->getStoreView();
        return $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($store->getTradingHours());
    }

    public function getRadius($storelocator = NULL)
    {
        //Return store radius
        if (!is_null($storelocator)) {
            if ($storelocator->getRadius()) {
                return $storelocator->getRadius();
            } else {
                // Return default config radius
                return 100;
            }
        } else {
            // Return default config radius
            return 100;
        }
        return;
    }

}
