<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Mpanel\Block\Products;

/**
 * Main contact form block
 */
class Tabs extends \FIRSTSHOW\Mpanel\Block\Products\Attributes
{
	public function getAttributes(){
		$result = [];
		if($this->hasData('attributes')){
			$attributeCodes = $this->getData('attributes');
			$attributeArray = explode(',',$attributeCodes);
			if(count($attributeArray)>0){
				foreach($attributeArray as $attributeCode){
					$result[] = $attributeCode;
				}
			}
		}
		return $result;
	}
	
	public function getTitles(){
		$result = [];
		if($this->hasData('titles')){
			$titles = $this->getData('titles');
			$titleArray = explode(',',$titles);
			if(count($titleArray)>0){
				foreach($titleArray as $title){
					$result[] = $title;
				}
			}
		}
		return $result;
	}
}

