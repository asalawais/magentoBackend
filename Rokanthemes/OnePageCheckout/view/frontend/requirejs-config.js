var config = {
    map: {
        '*': {
            'Magento_Checkout/template/billing-address/form.html':
                'Rokanthemes_OnePageCheckout/template/billing-address/form.html',
            'Magento_Checkout/js/model/shipping-rate-service':
                'Rokanthemes_OnePageCheckout/js/model/shipping-rate-service',
            'Magento_Checkout/js/action/get-payment-information':
                'Rokanthemes_OnePageCheckout/js/action/get-payment-information'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Rokanthemes_OnePageCheckout/js/model/place-order-mixin': true,
                'Magento_CheckoutAgreements/js/model/place-order-mixin': false
            },
            'Magento_Checkout/js/model/step-navigator': {
                'Rokanthemes_OnePageCheckout/js/model/step-navigator-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Magento_CheckoutAgreements/js/model/set-payment-information-mixin': false,
                'Rokanthemes_OnePageCheckout/js/model/set-payment-information-mixin': true
            },
            'Magento_Checkout/js/model/shipping-rates-validation-rules': {
                'Rokanthemes_OnePageCheckout/js/model/shipping-rates-validation-rules-mixin': true
            },
            'Magento_Paypal/js/in-context/express-checkout-wrapper': {
                'Rokanthemes_OnePageCheckout/js/paypal/in-context/express-checkout-wrapper-mixin': true
            },
            'Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express': {
                'Rokanthemes_OnePageCheckout/js/paypal/view/payment/method-renderer/in-context/checkout-express': true
            },
            'Amazon_Payment/js/view/payment/list': {
                'Rokanthemes_OnePageCheckout/js/amazon-pay/view/payment-list': true
            },
            'Amazon_Payment/js/view/checkout-revert': {
                'Rokanthemes_OnePageCheckout/js/amazon-pay/view/checkout-revert-rewrite': true
            }
        }
    }
};
