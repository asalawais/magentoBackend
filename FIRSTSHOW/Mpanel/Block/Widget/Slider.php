<?php
namespace FIRSTSHOW\Mpanel\Block\Widget;
 
class Slider extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	public function _toHtml()
    {
    	$this->setTemplate('widget/slider_firstshow.phtml');
		return parent::_toHtml();
    }
}