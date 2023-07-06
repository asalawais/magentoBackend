<?php
namespace Rokanthemes\OnePageCheckout\Plugin\Checkout\Model;

use Rokanthemes\OnePageCheckout\Model\AdditionalData;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestAdditionalData
{
    /**
     * @var AdditionalData
     */
    private $additionalDataModel;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * One step checkout helper
     *
     * @var Config
     */
    private $configHelper;

    /**
     * GuestAdditionalData constructor.
     * @param AdditionalData $additionalDataModel
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param \Rokanthemes\OnePageCheckout\Helper\Data $configHelper
     */
    public function __construct(
        AdditionalData $additionalDataModel,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Rokanthemes\OnePageCheckout\Helper\Data $configHelper
    ) {
        $this->additionalDataModel = $additionalDataModel;
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($paymentMethod->getExtensionAttributes() !== null
            && $this->configHelper->getModuleStatus()
            && $paymentMethod->getExtensionAttributes()->getRokanthemesOpc() !== null
        ) {
            $additionalData = $paymentMethod->getExtensionAttributes()->getRokanthemesOpc();
            $orderId = $proceed($cartId, $email, $paymentMethod, $billingAddress);
            if (!empty($additionalData) && isset($additionalData['order_comment'])) {
                $this->additionalDataModel->saveComment($orderId, $additionalData);
            }
            if (!empty($additionalData)
                && $this->configHelper->isDisplayField('show_subscribe_newsletter')
            ) {
                $this->additionalDataModel->subscriber($orderId, $additionalData);
            }
        } else {
            return $proceed($cartId, $email, $paymentMethod, $billingAddress);
        }
    }
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($paymentMethod->getExtensionAttributes() !== null
            && $this->configHelper->getModuleStatus()
            && $paymentMethod->getExtensionAttributes()->getRokanthemesOpc() !== null
        ) {
            $additionalData = $paymentMethod->getExtensionAttributes()->getRokanthemesOpc();
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
            if (!empty($additionalData)) {
                $this->additionalDataModel->saveDelivery($quote, $additionalData);
                if (in_array($paymentMethod->getMethod(), $this->configHelper->getPaymentOnlineMethods())) {
                    $this->checkoutSession->create()->setRokanthemesOpcAdditionalData($additionalData);
                }
            }
        }
    }
}
