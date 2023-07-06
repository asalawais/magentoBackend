define([
    'Magento_Checkout/js/model/quote',
    'Rokanthemes_OnePageCheckout/js/model/shipping-save-processor'
], function (quote, shippingSaveProcessor) {
    'use strict';

    return function () {
        return shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType());
    };
});
