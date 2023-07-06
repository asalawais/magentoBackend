<?php

namespace Twl\AdaptiveResize\Helper;
// use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $configurable;
    protected $grouped;
    protected $_productloader;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Catalog\Model\ProductFactory $_productloader
    )
    {
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->_productloader = $_productloader;
        parent::__construct($context);
    }

    public function getParentId($childId){
    /* for simple product of configurable product */
        $product = $this->configurable->getParentIdsByChild($childId);
        if(isset($product[0])){
           return $product[0];
        }else {
          return 0;
        }

    /* for simple product of Group product */
       $parentIds = $this->grouped->getParentIdsByChild($childId);
    /* or for Group/Bundle Product */
       $product->getTypeInstance()->getParentIdsByChild($childId);

    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}
