define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/loader',
    'Magento_Customer/js/customer-data'
], function ($, modal, loader, customerData) {
    'use strict';

    return function(config, node) {

        var product_id = jQuery(node).data('id');
        var product_url = jQuery(node).data('url');

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: false,
            title: $.mage.__('Find in store'),
			modalClass:'find-in-store-modal',
            buttons: [{
                text: $.mage.__('Close'),
                class: 'close-modal',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#quickViewContainer' + product_id));

        $("#quickViewButton" + product_id).on("click", function () {
            openQuickViewModal();
        });

        var openQuickViewModal = function () {
            var modalContainer = $("#quickViewContainer" + product_id);
             modalContainer.addClass("product-quickview");
                modalContainer.modal('openModal');
			modalContainer.html(createIframe());

            var iframe_selector = "#iFrame" + product_id;

            /*$(iframe_selector).on("load", function () {
                modalContainer.addClass("product-quickview");
                modalContainer.modal('openModal');
                observeAddToCart(iframe_selector);
            });*/
        };

        

        var createIframe = function () {
            return $('<iframe />', {
                id: 'iFrame' + product_id,
                src: product_url + "?iframe=1"
            });
        }
    };
});