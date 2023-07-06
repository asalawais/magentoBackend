<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Testimonial\Model\ResourceModel\Testimonial;

/**
 * Testimonial resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('FIRSTSHOW\Testimonial\Model\Testimonial', 'FIRSTSHOW\Testimonial\Model\ResourceModel\Testimonial');
    }
}
