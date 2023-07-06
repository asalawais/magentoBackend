define(['jquery','priceUtils'], function ($, priceUtils) {
    'use strict';

    return function (configurable) {
        $.widget('mage.configurable', $['mage']['configurable'], {
            /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
         options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            spConfig: {},
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
            '<% if (typeof data.finalPrice.value !== "undefined") { %>' +
            ' <%- data.finalPrice.formatted %>' +
            '<% } %>',
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',
            mediaGalleryInitial: null,
            slyOldPriceSelector: '.sly-old-price',
            normalPriceLabelSelector: '.normal-price .price-label',

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            qtySelector: '#qty'
        },

        _configureElement: function (element) {

            this.simpleProduct = this._getSimpleProductId(element);
             if (element.value) {
                this.options.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    if (!!document.documentMode) { //eslint-disable-line
                        this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);

                    } else {
                        this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                    }
                }
            } else {
                this._resetChildren(element);
            }

            this._reloadPrice();
            this._displayRegularPriceBlock(this.simpleProduct);
            this._displayTierPriceBlock(this.simpleProduct);
            this._displayNormalPriceLabel();
            this._changeProductImage();
            this._displayQtyBoxBlock(this.simpleProduct);
        },

            /**
             * Populates an option's selectable choices.
             * @private
             * @param {*} element - Element associated with a configurable option.
             */
            _fillSelect: function (element) {
                var attributeId = element.id.replace(/[a-z]*/, ''),
                    options = this._getAttributeOptions(attributeId),
                    prevConfig,
                    index = 1,
                    allowedProducts,
                    allowedProductsByOption,
                    allowedProductsAll,
                    i,
                    j,
                    finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                    optionFinalPrice,
                    optionPriceDiff,
                    optionPrices = this.options.spConfig.optionPrices,
                    allowedOptions = [],
                    indexKey,
                    allowedProductMinPrice,
                    allowedProductsAllMinPrice;

                this._clearSelect(element);
                element.options[0] = new Option('', '');
                element.options[0].innerHTML = this.options.spConfig.chooseText;
                prevConfig = false;

                if (element.prevSetting) {
                    prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
                }

                if (options) {
                    for (indexKey in this.options.spConfig.index) {
                        /* eslint-disable max-depth */
                        if (this.options.spConfig.index.hasOwnProperty(indexKey)) {
                            allowedOptions = allowedOptions.concat(_.values(this.options.spConfig.index[indexKey]));
                        }
                    }

                    if (prevConfig) {
                        allowedProductsByOption = {};
                        allowedProductsAll = [];

                        for (i = 0; i < options.length; i++) {
                            /* eslint-disable max-depth */
                            for (j = 0; j < options[i].products.length; j++) {
                                // prevConfig.config can be undefined
                                if (prevConfig.config &&
                                    prevConfig.config.allowedProducts &&
                                    prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                    if (!allowedProductsByOption[i]) {
                                        allowedProductsByOption[i] = [];
                                    }
                                    allowedProductsByOption[i].push(options[i].products[j]);
                                    allowedProductsAll.push(options[i].products[j]);
                                }
                            }
                        }

                        if (typeof allowedProductsAll[0] !== 'undefined' &&
                            typeof optionPrices[allowedProductsAll[0]] !== 'undefined') {
                            allowedProductsAllMinPrice = this._getAllowedProductWithMinPrice(allowedProductsAll);
                            finalPrice = parseFloat(optionPrices[allowedProductsAllMinPrice].finalPrice.amount);
                        }
                    }

                    for (i = 0; i < options.length; i++) {
                        if (prevConfig && typeof allowedProductsByOption[i] === 'undefined') {
                            continue; //jscs:ignore disallowKeywords
                        }

                        allowedProducts = prevConfig ? allowedProductsByOption[i] : options[i].products.slice(0);
                        optionPriceDiff = 0;

                        if (typeof allowedProducts[0] !== 'undefined' &&
                            typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                            allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                            optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                            optionPriceDiff = optionFinalPrice - finalPrice;
                            options[i].label = options[i].initialLabel;

                            /*if (optionPriceDiff !== 0) {
                                options[i].label += ' ' + priceUtils.formatPrice(
                                    optionPriceDiff,
                                    this.options.priceFormat,
                                    true
                                );
                            }*/
                        }

                        if (allowedProducts.length > 0 || _.include(allowedOptions, options[i].id)) {
                            options[i].allowedProducts = allowedProducts;
                            element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                            if (typeof options[i].price !== 'undefined') {
                                element.options[index].setAttribute('price', options[i].price);
                            }

                            if (allowedProducts.length === 0) {
                                element.options[index].disabled = true;
                            }

                            element.options[index].config = options[i];
                            index++;
                        }
                        // Code added to select option
                        if (i == 0) {
                            this.options.values[attributeId] = options[i].id;
                        }

                        /* eslint-enable max-depth */
                    }
                    //Code added to check if configurations are set in url and resets them if needed
                    if (window.location.href.indexOf('#') !== -1) {
                        this._parseQueryParams(window.location.href.substr(window.location.href.indexOf('#') + 1));
                    }
                }
            },

        /**
         * Show or hide tier price block
         *
         * @param {*} optionId
         * @private
         */
        _displayQtyBoxBlock: function (productId) {
            var description, size, color, productBrand, cornerHeight, closure, quality, gtin, articlecode, qtySelector;
            if (typeof productId != 'undefined' &&
                this.options.spConfig.qtyBoxes[productId] != [] // eslint-disable-line eqeqeq
            ) {
                description = this.options.spConfig.qtyBoxes[productId]['description'];
                size = this.options.spConfig.qtyBoxes[productId]['size'];
                color = this.options.spConfig.qtyBoxes[productId]['color'];
                productBrand = this.options.spConfig.qtyBoxes[productId]['product_brand'];
                cornerHeight = this.options.spConfig.qtyBoxes[productId]['corner_height'];
                closure = this.options.spConfig.qtyBoxes[productId]['closure'];
                quality = this.options.spConfig.qtyBoxes[productId]['quality'];
                gtin = this.options.spConfig.qtyBoxes[productId]['gtin'];
                articlecode = this.options.spConfig.qtyBoxes[productId]['articlecode'];
                if (description == null){
                    description = '';
                }
                if (color == null){
                    color = '-';
                }
                if (productBrand == null){
                    productBrand = '-';
                }
                if (cornerHeight == null){
                    cornerHeight = '-';
                }
                if (closure == null){
                    closure = '-';
                }
                if (quality == null){
                    quality = '-';
                }
                if (this.options.qtySelector) {
                    //$(this.options.qtySelector).val(qtyBox).show();
                    //$('div.field div.control #qty_incdec').val(qtyBox);
                    var data = '<div className="product data items" role="tablist">\n' +
                        '                    <div className="data item content" aria-labelledby="tab-label-description" id="description"\n' +
                        '                         data-role="content" role="tabpanel" aria-hidden="false" style="display: block;">\n' +
                        '\n' +
                        '                        <div className="product attribute description">\n' +
                        '                            <div className="value">\n' +
                                description + '\n' +
                        '                            </div>\n' +
                        '                        </div>\n' +
                        '                    </div>\n' +
                        '                    <div className="data item content" aria-labelledby="tab-label-additional" id="additional"\n' +
                        '                         data-role="content" role="tabpanel" aria-hidden="true">\n' +
                        '                        <div className="additional-attributes-wrapper table-wrapper">\n' +
                        '                            <table className="data table additional-attributes" id="product-attribute-specs-table">\n' +
                        '                                <caption className="table-caption">Meer informatie</caption>\n' +
                        '                                <tbody>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Merk</th>\n' +
                        '                                    <td className="col data" data-th="Merk">' + productBrand + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Maat</th>\n' +
                        '                                    <td className="col data" data-th="Maat">' + size + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Kleur</th>\n' +
                        '                                    <td className="col data" data-th="Kleur">' + color + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Sluiting</th>\n' +
                        '                                    <td className="col data" data-th="Sluiting">' + closure + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Hoekhoogte</th>\n' +
                        '                                    <td className="col data" data-th="Hoekhoogte">'+ cornerHeight + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">Kwaliteit</th>\n' +
                        '                                    <td className="col data" data-th="Kwaliteit">' + quality + '</td>\n' +
                        '                                </tr>\n' +
                        '                                <tr>\n' +
                        '                                    <th className="col label" scope="row">GTIN</th>\n' +
                        '                                    <td className="col data" data-th="gtin">' + gtin + '</td>\n' +
                        '                                </tr>\n' +
                        '                                 <tr>\n' +
                        '                                    <th className="col label" scope="row">Artikelcode</th>\n' +
                        '                                    <td className="col data" data-th="articlecode">' + articlecode + '</td>\n' +
                        '                                </tr>\n' +
                        '                                </tbody>\n' +
                        '                            </table>\n' +
                        '                        </div>\n' +
                        '                    </div>\n' +
                        '                </div>';

                    $('div.product.info.detailed').html(data).load(); //this._updateQtyBox(qtyBox);
                }

            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        },

        _updateQtyBox: function (qtyBox){
            var number_click = qtyBox;
            //alert(qtyBox);
                $(".qty-down-fixed-onclick").click(function() {
                    var val_input = $(this).closest('div.field').find('#qty').val();
                    val_input = parseInt(val_input);
                    if(val_input <= number_click){
                        val_input = number_click;
                    }
                    else{
                        val_input = val_input - number_click;
                    }
                    $('div.field div.control #qty').val(val_input);
                    return false;
                });
                $(".qty-up-fixed-onclick").click(function(e) {
                    e.preventDefault();
                    var val_input = $(this).closest('div.field').find('#qty').val();
                    val_input = parseInt(val_input);
                    //alert(val_input);
                    val_input = val_input + number_click;
                    $('div.field div.control #qty').val(val_input);
                    return false;
                });
            }

        });
        return $['mage']['configurable'];
    };
});
