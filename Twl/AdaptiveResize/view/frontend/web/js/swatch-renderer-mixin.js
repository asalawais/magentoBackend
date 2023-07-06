define([
    'jquery',
    'firstshow/owlcarousel',
    'firstshow/fslightbox'
], function ($) {
    'use strict';

    return function (widget) {
        // console.log('Hello from SwatchExtend');

        $.widget('mage.SwatchRenderer', widget, {

          _init: function () {
                // console.log('I m in INit');
                // Don't render the same set of swatches twice
                if ($(this.element).attr('data-rendered')) {
                    return;
                }
                $(this.element).attr('data-rendered', true);

                if (_.isEmpty(this.options.jsonConfig.images)) {
                    this.options.useAjax = true;
                    // creates debounced variant of _LoadProductMedia()
                    // to use it in events handlers instead of _LoadProductMedia()
                    this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
                }

                if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                    // store unsorted attributes
                    this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                    this._sortAttributes();
                    this._RenderControls();
                    //this is additional code for select first attribute value
                    var productData = this._determineProductData();
                    if (this.options.jsonConfig.attributes.length > 0) {
                      // console.log('this.element' + this.element.html());
                      // console.log('this.options.classes.attributeOptionsWrapper:: ' + this.options.classes.attributeOptionsWrapper);
                        // var selectswatch = this.element.find('.' + this.options.classes.attributeClass + '.color .' + this.options.classes.attributeOptionsWrapper);
                        // var selectswatch = this.element.find('.swatch-attribute-options.p-list');
                        var selectswatch = this.element.find('.swatch-attribute-options');
                        // console.log('selectswatch::' + selectswatch.length);
                        if (productData.isInProductView) {
                          	var swatchSizeAttr = this.element.find('.swatch-attribute.size');
                          	swatchSizeAttr.prepend('<a href="#sizeGuideModal" title="Size Guide" class="size_guide-link js-size_guide-link" data-toggle="modal" data-target="#sizeGuideModal" data-preselect="shoes"> Size Guide </a>');
						  	var query = window.location.search.substring(1);
							//console.log(query);
						   	var vars = query.split("&");
						   	for (var i=0;i<vars.length;i++) {
								   var pair = vars[i].split("=");
								   if(pair[0] == 'color'){
									   
									   var colorSwatchOption = $(selectswatch).find('div.swatch-option.color[option-label='+ pair[1] +']');
									   colorSwatchOption.trigger('click');
									   //return pair[1];
									}
						   	}
                        }
                        $.each(selectswatch, function (index, item) {
                          if (productData.isInProductView) {
                            var swatchOption = $(item).find('div.swatch-option.color').first();
                             // && !$(item).find('div.swatch-option').hasClass('selected')
                             // console.log('first color option' + swatchOption);
                            if (swatchOption.length  && !$(item).find('div.swatch-option').hasClass('selected')) {
                                //swatchOption.trigger('click');
                            }
                          }else {
                            var child_selected_id = $(item).parents('.child_selected_id').attr('child_selected_id');
                            if (child_selected_id != "") {
                               //console.log($(item).find('div.swatch-option#'+child_selected_id).length);
                              if ($(item).find('div.swatch-option#'+child_selected_id).length > 0) {
                                var swatchOption = $(item).find('div.swatch-option#'+child_selected_id)
                                swatchOption.trigger('click'); //category click
                              }
                            }else  {
                              var swatchOption = $(item).find('div.swatch-option').first();
                               // && !$(item).find('div.swatch-option').hasClass('selected')
                              if (swatchOption.length  && !$(item).find('div.swatch-option').hasClass('selected')) {
                                  //swatchOption.trigger('click');
                              }
                            }
                          }


                            // console.log("First" + swatchOption);
                            // console.log("swatchOption.length" + swatchOption.length);
                            // console.log("selected" + $(item).find('div.swatch-option').hasClass('selected'));
                            // if (swatchOption.length && !$(item).find('div.swatch-option').hasClass('selected')) {
                        });

                        // console.log('body.catalog-poroduct-view' + productData.isInProductView);
                        if (productData.isInProductView) {

                        }else {
                          // var sizeSwatch = $('.products-grid .swatch-attribute.size').find('.swatch-attribute-options.p-list');
                          var sizeSwatch = $('.products-grid .swatch-attribute.size').find('.swatch-attribute-options');
                          // console.log($('.swatch-attribute.size').length);
                          sizeSwatch.owlCarousel({
                            items: 3,
                            loop: false,
                            nav: true,
                            dots: false,
                            pagination: false,
                            autoplayTimeout: 6000,
                            navText: ["<i class='fa fa-angle-left'	></i>","<i class='fa fa-angle-right'></i>"],
                            autoplay: false
                          });
                        }

                    }
                    // this._setPreSelectedGallery();
                    $(this.element).trigger('swatch.initialized');
                } else {
                    // console.log('SwatchRenderer: No input data received');
                }
                this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
            },
            _Rebuild: function () {
                // console.log('Hello from rebuild method');
                return this._super();
            },
            _create: function () {
              // console.log('I m in _create');
                var options = this.options,
                    gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                    productData = this._determineProductData(),
                    $main = productData.isInProductView ?
                        this.element.parents('.column.main') :
                        this.element.parents('.product-item-info');

                if (productData.isInProductView) {
                    gallery.data('gallery') ?
                        this._onGalleryLoaded(gallery) :
                        gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
                } else {
                    options.mediaGalleryInitial = [{
                        'img': $main.find('.product-image-photo').attr('src')
                    }];
                }

                this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
                this.inProductList = this.productForm.length > 0;
            },
            /**
             * Update [gallery-placeholder] or [product-image-photo]
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isInProductView
             */
            updateBaseImage: function (images, context, isInProductView) {
               //console.log('I m in updateBaseImage');
               //console.log(images);
               //console.log('justAnImage');
                var justAnImage = images[0],
                    initialImages = this.options.mediaGalleryInitial,
                    imagesToUpdate,
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    isInitial;
                var item = '';

                if (isInProductView) {
                  item += "<ol>";
                  if (images) {
                    for (var i = 0; i < images.length; i++) {
                      item += '<li class="img-list-item"><a href="'+images[i].full+'" data-fslightbox="lightbox" data-type="image"><img src="'+images[i].full+'" alt="'+title+'" /></a></li>';
                    }
                  }
                  item += "</ol>";
                  $('.gallery-placeholder').html(item);

                  fsLightboxInstances['lightbox'].props.onOpen = function () {
                    var fullscreen = $('.fslightbox-toolbar  > .fslightbox-toolbar-button:eq(0)');
                    fullscreen.trigger('click');
                  }
                  fsLightboxInstances['lightbox'].props.exitFullscreenOnClose = true;
                  refreshFsLightbox();

                    // imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                    // isInitial = _.isEqual(imagesToUpdate, initialImages);
                    //
                    // if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                    //     imagesToUpdate = imagesToUpdate.concat(initialImages);
                    // }
                    //
                    // imagesToUpdate = this._setImageIndex(imagesToUpdate);
                    //
                    // if (!_.isUndefined(gallery)) {
                    //     gallery.updateData(imagesToUpdate);
                    // } else {
                    //     context.find(this.options.mediaGallerySelector).on('gallery:loaded', function (loadedGallery) {
                    //         loadedGallery = context.find(this.options.mediaGallerySelector).data('gallery');
                    //         loadedGallery.updateData(imagesToUpdate);
                    //     }.bind(this));
                    // }
                    //
                    // if (isInitial) {
                    //     $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    // } else {
                    //     $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                    //         selectedOption: this.getProduct(),
                    //         dataMergeStrategy: this.options.gallerySwitchStrategy
                    //     });
                    // }
                    //
                    // if (gallery) {
                    //     gallery.first();
                    // }


                } else if (justAnImage && justAnImage.img) {
                    // context.find('.product-image-photo').attr('src', justAnImage.img);
                    var carouselContainer = context.find('.image-container-items.only-configurable-product');
                    var productURl = carouselContainer.attr('product_url');
                    var title = carouselContainer.attr('title');
					var colorSwatchOption = context.find('div.swatch-option.color.selected');
					var colorSelected = '';
					if(colorSwatchOption.attr('option-label')){
						colorSelected = '?color='+colorSwatchOption.attr('option-label');
					}
                    // carouselContainer.html('');
                    // console.log(justAnImage);
                    // console.log(images);
                    if (images) {
                      for (var i = 0; i < images.length; i++) {
                        item += '<div class="product-image-item"><a href="'+productURl+colorSelected+'" class="product-item-photo"><img src="'+images[i].img+'" alt="'+title+'" class="img-responsive product-image-photo-new img-thumbnail"/></a></div>';
                      }
                    }
                    // console.log(item);
                    carouselContainer.html(item);

                    var innerCarousel = $(carouselContainer);
                    if(typeof innerCarousel.data('owlCarousel') != 'undefined') {
                      innerCarousel.data('owlCarousel').destroy();
                      innerCarousel.removeClass('owl-carousel');
                    }
                    // innerCarousel.trigger('destroy.owl.carousel');
                    innerCarousel.owlCarousel({
            					items: 1,
            					loop: false,
            					nav: true,
            					dots: false,
                      pagination: false,
            					autoplayTimeout: 6000,
            					navText: ["<i class='fa fa-angle-left'	></i>","<i class='fa fa-angle-right'></i>"],
            					autoplay: false,
            					// responsive:{
            					// 	0 : {items: 1},
            					// 	480 : {items: 1},
            					// 	768 : {items: 3},
            					// 	980 : {items: 4},
            					// 	1200 : {items: 5}
            					// }
            				});
                    innerCarousel.on('changed.owl.carousel', function(event) {
                        // console.log('changed.owl.carousel');

                    })

                    // owl.trigger('refresh.owl.carousel');


                }
            },
            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
              // console.log('option click event fired _OnClick');
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    attributeId = $parent.attr('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput),
                    checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']);

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.hasClass('disabled')) {
                    return;
                }

                if ($this.hasClass('selected')) {
                    return;
                    $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                    $input.val('');
                    $label.text('');
                    $this.attr('aria-checked', false);
                } else {
                    $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                    $label.text($this.attr('option-label'));
                    $input.val($this.attr('option-id'));
                    $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                    $this.addClass('selected');
                    $widget._toggleCheckedAttributes($this, $wrapper);
                }

                $widget._Rebuild();

                if ($widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                ) {
                    $widget._UpdatePrice();
                }

                $(document).trigger('updateMsrpPriceBlock',
                    [
                        _.findKey($widget.options.jsonConfig.index, $widget.options.jsonConfig.defaultValues),
                        $widget.options.jsonConfig.optionPrices
                    ]);

                if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                    $widget._loadMedia();
                }

                $input.trigger('change');
            },

            /**
             * Event for select
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnChange: function ($this, $widget) {
              // console.log('option _OnChange event fired _OnChange');
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    attributeId = $parent.attr('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput);

                if ($widget.productForm.length > 0) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    );
                }

                if ($this.val() > 0) {
                    $parent.attr('option-selected', $this.val());
                    $input.val($this.val());
                } else {
                    $parent.removeAttr('option-selected');
                    $input.val('');
                }

                $widget._Rebuild();
                $widget._UpdatePrice();
                $widget._loadMedia();
                $input.trigger('change');
            },



        });

        return $.mage.SwatchRenderer;
    }
});
