define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'mage/url',
    'text!BigBridge_ProductImport/template/grid/cells/order/afasimport.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, url, sendmailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        gethtml: function (row) {
            return row[this.index + '_html'];
        },
        getFormaction: function (row) {
            return row[this.index + '_formaction'];
        },
        getEntityid: function (row) {
            return row[this.index + '_entityid'];
        },
        getUrl: function (row) {
            return row[this.index + '_url']
        },
        getLabel: function (row) {
            return row[this.index + '_html']
        },
        getTitle: function (row) {
            return row[this.index + '_title']
        },
        getSubmitlabel: function (row) {
            return row[this.index + '_submitlabel']
        },
        getCancellabel: function (row) {
            return row[this.index + '_cancellabel']
        },
        preview: function (row) {
            this.process(this.getUrl(row), row['entity_id']);
            require('uiRegistry').get('index = sales_order_columns')
                .source
                .reload({'refresh': true})
           /* var modalHtml = mageTemplate(
                sendmailPreviewTemplate,
                {
                    html: this.gethtml(row),
                    title: this.getTitle(row),
                    label: this.getLabel(row),
                    formaction: this.getFormaction(row),
                    entityid: this.getEntityid(row),
                    submitlabel: this.getSubmitlabel(row),
                    cancellabel: this.getCancellabel(row),
                    linkText: $.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: this.getTitle(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');*/
        },
        process : function(viewUrl, entityId) {
            if (viewUrl && entityId) {
                alert(viewUrl);
                alert(entityId);
                $.ajax({
                    url: viewUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: entityId
                    },
                    showLoader: true,
                    success: function(data) {
                        alert(JSON.stringify(data));
                    }.bind(this)
                });
            }
        },
        getFieldHandler: function (row) {
            //return row[this.index + '_html'];
            return this.preview.bind(this, row);
        }
    });
});
