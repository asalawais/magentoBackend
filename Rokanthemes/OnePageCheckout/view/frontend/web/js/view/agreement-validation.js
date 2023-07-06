define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Rokanthemes_OnePageCheckout/js/model/agreement-validator'
], function (Component, additionalValidators, agreementValidator) {
    'use strict';

    additionalValidators.registerValidator(agreementValidator);

    return Component.extend({});
});
