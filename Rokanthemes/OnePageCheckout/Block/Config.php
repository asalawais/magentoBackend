<?php
namespace Rokanthemes\OnePageCheckout\Block;


class Config extends \Magento\Framework\View\Element\Template
{
   protected $helper;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Rokanthemes\OnePageCheckout\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }
    public function getShortDescription()
    {
        return $this->helper->getGeneral('short_description');
    }
}
