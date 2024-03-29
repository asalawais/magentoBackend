<?php

namespace FIRSTSHOW\StoreLocator\Block\Locator\Grid\Options;

class StoreOptionHash implements \Magento\Framework\Option\ArrayInterface {

    /**
     * System Store Model
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(\Magento\Store\Model\System\Store $systemStore) {
        $this->_systemStore = $systemStore;
    }

    /**
     * Return store array
     *
     * @return array
     */
    public function toOptionArray() {
        return $this->_systemStore->getStoreOptionHash();
    }

}
