<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Import extends \FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel
{
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	protected $_filesystem;
	
	protected $_xmlArray;
	protected $_home;
	protected $_theme;
	
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\Magento\Config\Model\Config\Factory $configFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Stdlib\StringUtils $string
	){
        parent::__construct($context);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if(($this->_theme = $this->getRequest()->getParam('theme')) && ($this->_home = $this->getRequest()->getParam('home'))){
			$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/FIRSTSHOW/Mpanel/data/themes/'.$this->_theme.'/homes');
			$homepageFile = $dir.'/'.$this->_home.'.xml';
			
			
			if (is_readable($homepageFile)){
				$this->_xmlArray = $this->_parser->load($homepageFile)->xmlToArray();
				
				try {
					$this->importStaticBlock();
					$this->importPromoBanner();
					$homepageIdentifier = $this->importHomepageContent();
					
					if($homepageIdentifier){
						// Set Homepage Default
						$section = 'web';
						$website = $this->getRequest()->getParam('website');
						$store = $this->getRequest()->getParam('store');
						
						$groups = [
							'default'=> [
								'fields' => [
									'cms_home_page' => [
										'value' => $homepageIdentifier
									]
								]
							]
						];

						$configData = [
							'section' => $section,
							'website' => $website,
							'store' => $store,
							'groups' => $groups
						];

						/** @var \Magento\Config\Model\Config $configModel  */
						$configModel = $this->_configFactory->create(['data' => $configData]);
						$configModel->save();
						
						/* Import Theme Setting And Color Setting*/
						$this->_importSetting();
						
						$this->messageManager->addSuccess(__('%1 was successfully imported.', $this->convertString($this->_home)));
					}else{
						$this->messageManager->addError(__('Cannot set homepage default.'));
					}
				}catch (\Exception $e) {
					// display error message
					$this->messageManager->addError($e->getMessage());
				}
			}else{
				$this->messageManager->addError(__('Cannot import this homepage.'));
			}
		}else{
			$this->messageManager->addError(__('This homepage no longer exists.'));
		}
		$this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
	
	public function convertString($theme){
		$themeName = str_replace('_',' ',$theme);
		return ucfirst($themeName);
	}
	
	/* Import Static Blocks */
	public function importStaticBlock(){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home']['static_block']['item']) && (count($parsedArray['home']['static_block']['item'])>0)){
			foreach($parsedArray['home']['static_block']['item'] as $staticBlock){
				if(is_array($staticBlock)){
					$identifier = $staticBlock['identifier'];
					$staticBlockData = $staticBlock;
				}else{
					$identifier = $parsedArray['home']['static_block']['item']['identifier'];
					$staticBlockData = $parsedArray['home']['static_block']['item'];
				}
				
				$staticBlocksCollection = $this->_objectManager->create('Magento\Cms\Model\Block')
					->getCollection()
					->addFieldToFilter('identifier', $identifier)
					->load();
				if (count($staticBlocksCollection) > 0){
					foreach ($staticBlocksCollection as $_item){
						$_item->delete();
					}
				}
				
				$this->_objectManager->create('Magento\Cms\Model\Block')->setData($staticBlockData)->setIsActive(1)->setStores(array(0))->save();
				
			}
		}
	}
	
	/* Import Homepage Content */
	public function importHomepageContent(){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home']['cms_page'])){
			$identifier = $parsedArray['home']['cms_page']['identifier'];
			$cmsPageData = $parsedArray['home']['cms_page'];
			
			$cmsPageCollection = $this->_objectManager->create('Magento\Cms\Model\Page')
				->getCollection()
				->addFieldToFilter('identifier', $identifier)
				->load();
			
			if (count($cmsPageCollection) > 0){
				foreach ($cmsPageCollection as $_item){
					$_item->delete();
				}
			}
			
			$this->_objectManager->create('Magento\Cms\Model\Page')->setData($cmsPageData)->setIsActive(1)->setStores(array(0))->save();
			
			return $identifier;
		}
		return false;
	}
	
	/* Import Homepage Content */
	public function importPromoBanner(){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home']['promo_banner']['item'])){
			foreach($parsedArray['home']['promo_banner']['item'] as $banner){
				if(is_array($banner)){
					$identifier = $banner['identifier'];
					$bannerData = $banner;
				}else{
					$identifier = $parsedArray['home']['promo_banner']['item']['identifier'];
					$bannerData = $parsedArray['home']['promo_banner']['item'];
				}
				
				$banners = $this->_objectManager->create('FIRSTSHOW\Promobanners\Model\Promobanners')
					->getCollection()
					->addFieldToFilter('identifier', $identifier);
				if (count($banners) > 0){
					foreach ($banners as $_banner){
						$_banner->delete();
					}
				}
				
				$this->_objectManager->create('FIRSTSHOW\Promobanners\Model\Promobanners')->setData($bannerData)->save();
				
			}
		}
		return;
	}
	
	public function _importSetting(){
		/* Import Theme Setting */
		$this->imporSetting('theme_setting', 'firstshowtheme');
		
		/* Import Color Setting */
		$this->imporSetting('color_setting', 'color');
		
		/* Import Panel Setting */
		$this->imporSetting('panel_setting', 'mpanel');
		
		return;
	}
	
	public function imporSetting($xmlNode, $section){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home'][$xmlNode])){
			$website = $this->getRequest()->getParam('website');
			$store = $this->getRequest()->getParam('store');
			$groups = [];
			if(count($parsedArray['home'][$xmlNode])>0){
				foreach($parsedArray['home'][$xmlNode] as $groupName=>$_group){
					$fields = [];
					foreach($_group as $field=>$value){
						//if($value!=''){
							$fields[$field] = ['value'=>$value];
						//}
					}
					
					$groups[$groupName] = [
						'fields' => $fields
					];
				}
			}
			
			$configData = [
				'section' => $section,
				'website' => $website,
				'store' => $store,
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
		}
		return;
	}
	
	/**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->_string->upperCaseWords('design', '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }
}
