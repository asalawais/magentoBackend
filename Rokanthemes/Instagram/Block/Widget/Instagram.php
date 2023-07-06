<?php

namespace Rokanthemes\Instagram\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Instagram extends Template  implements BlockInterface
{
	protected $_instagrampostFactory;
	protected $_storeManager;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Rokanthemes\Instagram\Model\InstagrampostFactory $instagrampostFactory,
        array $data = []
    ) {
    	$this->_storeManager = $storeManager;
    	$this->_instagrampostFactory =  $instagrampostFactory;
        parent::__construct($context, $data);
        $this->setTemplate('widget/instagram.phtml');
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    public function getInstagramPostByStoreView()
    {
    	$store_id = $this->getStoreId();
    	$post_in = $this->_instagrampostFactory->create();
    	$collection = $post_in->getCollection()->addFieldToFilter('store',['eq' => $store_id]);
        if($collection->count() <= 0){
            $collection_default = $post_in->getCollection()->addFieldToFilter('store',['eq' => 0]);
            return $collection_default;
        }
    	return $collection;
    }

}