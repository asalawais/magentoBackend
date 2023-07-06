<?php

namespace FIRSTSHOW\Brand\Model\Source;

class Template implements \Magento\Framework\Option\ArrayInterface
{
    protected $brandModel;

    public function __construct(\FIRSTSHOW\Brand\Model\Brand $brandModel)
    {
        $this->brandModel = $brandModel;
    }

    public function toOptionArray()
    {
        $options = array(
            array(
                'label' => __('Default Template'),
                'value' => 'widget/default.phtml'
            )
        );
        return $options;
    }
}