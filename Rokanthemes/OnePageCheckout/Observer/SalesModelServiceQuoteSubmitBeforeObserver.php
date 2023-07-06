<?php
namespace Rokanthemes\OnePageCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var \Rokanthemes\OnePageCheckout\Helper\Data|Config
     */
    private $configHelper;

    public function __construct(
        \Rokanthemes\OnePageCheckout\Helper\Data $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        if ($this->configHelper->getModuleStatus()) {
            $order->setCustomerShippingDate($quote->getCustomerShippingDate());
            $order->setCustomerShippingComments($quote->getCustomerShippingComments());
        }
        return $this;
    }
}
