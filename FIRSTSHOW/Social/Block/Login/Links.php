<?php

namespace FIRSTSHOW\Social\Block\Login;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use FIRSTSHOW\Social\Helper\Data as SocialHelper;

class Links extends Template
{
    protected $socialHelper;

    public function __construct(
        Context $context,
        SocialHelper $socialHelper,
        array $data = []
    )
    {
        $this->socialHelper = $socialHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}