<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace FIRSTSHOW\Mpanel\Controller\Index;

class Loadmore extends \Magento\Framework\App\Action\Action
{
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Element\Context $urlContext)     
	{
		$this->_urlBuilder = $urlContext->getUrlBuilder();

		parent::__construct($context);
	}
	
    public function execute()
    {
		$type = $this->getRequest()->getParam('type');
		$p = $this->getRequest()->getParam('p');
		$nextPage = $p+1;
		$count = $this->getRequest()->getParam('products_count');
		$perrow = $this->getRequest()->getParam('perrow');

		
		switch ($type) {
			case 'category-tabs':
				$categoryId = $this->getRequest()->getParam('category');
				$category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
				$html = $this->_view->getLayout()
					->createBlock('FIRSTSHOW\Mpanel\Block\Products\Category\Tabs')
					->setAdditionalData($category)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($p)
					->setTemplate('products/items.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('FIRSTSHOW\Mpanel\Block\Products\Category\Tabs')
					->setAdditionalData($category)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($nextPage)
					->setTemplate('products/items.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'category'=>$categoryId, 'products_count'=>$count, 'perrow'=>$perrow, 'p'=>$nextPage]);
				}
				break;
				
			case 'attribute-tabs':
				$attributeCode = $this->getRequest()->getParam('attribute');
				$html = $this->_view->getLayout()
					->createBlock('FIRSTSHOW\Mpanel\Block\Products\Tabs')
					->setAdditionalData($attributeCode)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($p)
					->setTemplate('products/items.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('FIRSTSHOW\Mpanel\Block\Products\Tabs')
					->setAdditionalData($attributeCode)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($nextPage)
					->setTemplate('products/items.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'attribute'=>$attributeCode, 'products_count'=>$count, 'perrow'=>$perrow, 'p'=>$nextPage]);
				}
				break;
		}
		
		$result['content'] = $html;
		echo json_encode($result);
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
}
