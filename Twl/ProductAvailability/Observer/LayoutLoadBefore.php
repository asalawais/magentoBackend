<?php
/**
 * @category    Twl
 * @package     Twl_ProductAvailability
 * @author      Ashwini Dinker <dinkeronline@gmail.com>
 * @copyright   Copyright (c) 2021 FirstShow (https://www.firshshow.sa)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Twl\ProductAvailability\Observer;

use Magento\Framework\View\Page\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Defines the implementaiton class
 */
class LayoutLoadBefore implements ObserverInterface

{
	
	
	/**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
	
    public function __construct(
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\RequestInterface $request
		){
        $this->_registry = $registry;
		$this->_request = $request;
	}
	
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->_registry->registry('current_product');
        if (!$product){
          return $this;
        }
         if ($this->_request->getParam("iframe")) {
           $layout = $observer->getLayout();
           $layout->getUpdate()->addHandle('iframe_product_view');
        }
		
        return $this;
    }
	
	
}
