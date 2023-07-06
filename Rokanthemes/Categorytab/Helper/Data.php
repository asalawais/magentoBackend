<?php

namespace Rokanthemes\Categorytab\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager= $objectManager;
        parent::__construct($context);
    }
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getModel($model) {
        return $this->_objectManager->create($model);
    }
    public function getCurrentStore() {
        return $this->_storeManager->getStore();
    }
	public function getThumbnailImageUrl($category)
    {
        $url   = $category->getImageUrl('cat_image_thumbnail');
        if($url == ''){
            $image = $category->getCatImageThumbnail();
            if ($image) {
                if (is_string($image)) {
                    if (strpos($image, 'catalog/tmp/category') !== false) {
                        $url = $this->_storeManager->getStore()->getBaseUrl() . $image;
                    }
                    else{
                        $url = $this->_storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . 'catalog/category/' . $image;
                    }
                } else {
                    $url = false;
                }
            }
        }
	    if($url && $url != ''){
            if (strpos($url, 'pub') !== false) {
                $url_tr = explode("pub", $url);
                if(isset($url_tr[1])){
                    $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) .'pub'.$url_tr[1];
                }
            }
        }
        else{
            $url = 'https://via.placeholder.com/150';
        }
		return $url;
    }
    public function getIconImageUrl($category)
    {
        $url   = false;
        $image = $category->getData('rt_menu_icon_img');
        if ($image) {
            if (is_string($image)) {
                /*$url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $image;*/
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_WEB
                    ) . $image;
            } else {
                $url = false;
            }
        }

        return $url;
    }
}
