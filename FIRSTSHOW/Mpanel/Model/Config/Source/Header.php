<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace FIRSTSHOW\Mpanel\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Header implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageHelper;
	
	protected $_storeManager;
	
	protected $request;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_filesystem = $filesystem;
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_objectManager = $objectManager;
		$this->request = $request;
    }
	
	public function getStoreConfig($node){
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
	}
	
	public function getRequest(){
		return $this->request;
	}
	
	public function getModel(){
		return $this->_objectManager->create('Magento\Theme\Model\Theme');
	}
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
		if($websiteId = $this->getRequest()->getParam('website')){
			$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId);
		}
		if($storeId = $this->getRequest()->getParam('store')){
			$themeId = $this->_scopeConfig->getValue('design/theme/theme_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		
		$theme = $this->getModel()->load($themeId);
		$themePath = $theme->getThemePath();
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('design/frontend/'.$themePath.'/Magento_Theme/templates/html/headers');
		
		$result = [];
		$files = [];
		if(is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
				sort($files);
				foreach ($files as $file){
					$file_parts = pathinfo($dir . $file);
					if (isset($file_parts['extension']) && $file_parts['extension'] == 'phtml') {
                        $fileName = str_replace('.phtml', '', $file);
                        $result[] = array('value' => $fileName, 'label' => $this->convertFilename($fileName));
                    }
				}
                closedir($dh);
            }
        }
		
        return $result;
    }
	
	public function convertFilename($filename){
		$filename = str_replace('_',' ',$filename);
		$filename = ucfirst($filename);
		return $filename;
	}
}
