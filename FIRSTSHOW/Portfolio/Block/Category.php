<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Portfolio\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Category extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context, array $data = [], 
		\Magento\Framework\ObjectManagerInterface $objectManager
	)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
    }
	
	/**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
		$title = __('Portfolio List');

		if($id = $this->getRequest()->getParam('id')){
			$category = $this->getModel()->load($id);
			$title = $category->getCategoryName();
		}
		
		$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbsBlock->addCrumb(
			'home',
			[
				'label' => __('Home'),
				'title' => __('Go to Home Page'),
				'link' => $this->_storeManager->getStore()->getBaseUrl()
			]
		);
		
		$breadcrumbsBlock->addCrumb('portfolio_category', ['label' => $title, 'title' => $title]);
		
        $this->pageConfig->getTitle()->set($title);
        return parent::_prepareLayout();
    }
	
	public function getModel(){
		return $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\Category');
	}
	
	public function getPortfolios(){
		$portfolios = $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\Portfolio')
			->getCollection()
			->addFieldToFilter('status', 1);
		
		if($id = $this->getRequest()->getParam('id')){
			$resourceModel = $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\ResourceModel\Portfolio');
			$portfolios = $resourceModel->joinFilter($portfolios, $id);
		}
		
		/* foreach ($portfolios as $portfolio) {
            $portfolio->setAddress($this->getUrl($this->helper('portfolio')->getPortfolioUrl($portfolio)));
        } */
		
		return $portfolios;
	}
	
	public function getPortfolioAddress($portfolio){
		$identifier = $portfolio->getIdentifier();
		if($identifier!=''){
			return $this->getUrl('portfolio/'.$identifier);
		}
		return $this->getUrl('portfolio/index/view', ['id'=>$portfolio->getId()]);
	}
	
	public function getThumbnailSrc($portfolio){
		$filePath = 'firstshow/portfolio/thumbnail/'.$portfolio->getThumbnailImage();
		if($filePath!=''){
			$thumbnailUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $filePath;
			return $thumbnailUrl;
		}
		return 0;
	}
	
	public function getCategories($portfolio){
		$collection = $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\Stores')
			->getCollection()
			->addFieldToFilter('portfolio_id', $portfolio->getId());
		
		$resourceModel = $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\ResourceModel\Stores');
		$collection = $resourceModel->joinFilter($collection);
		return $collection;
	}
	
	public function getCategoriesText($portfolio){
		$collection = $this->getCategories($portfolio);
		
		if(count($collection)>0){
			$arrResult = [];
			foreach($collection as $item){
				$arrResult[] = $item->getName();
			}
			return implode(', ', $arrResult);
		}
		return '';
	}
	
	public function getCategoriesLink($portfolio){
		$collection = $this->getCategories($portfolio);
		$html = '';
		if(count($collection)>0){
			$i=0;
			foreach($collection as $item){
				$cate = $this->_objectManager->create('FIRSTSHOW\Portfolio\Model\Category')->getCollection()->addFieldToFilter('category_id', ['eq' => $item->getCategoryId()])->getFirstItem();
				$i++;
				if($cate->getIdentifier()!=''){
					$html .= '<a href="'.$this->getUrl('portfolio/'.$cate->getIdentifier()).'">'.$item->getName().'</a>';
				}else{
					$html .= '<a href="'.$this->getUrl('portfolio/category/view', ['id'=>$cate->getId()]).'">'.$item->getName().'</a>';
				}
				
				if($i<count($collection)){
					$html .= ', ';
				}
			}
		}
		return $html;
	}
	
	public function getMenu(){
		$menu = $this->getModel()->getCollection();

		foreach ($menu as $cate) {
			if($cate->getIdentifier()!=''){
				$cate->setLinkCate($this->getUrl('portfolio/'.$cate->getIdentifier()));
			}else{
				$cate->setLinkCate($this->getUrl('portfolio/category/view', ['id'=>$cate->getId()]));
			}
            
        }
		return $menu;
	}
}

