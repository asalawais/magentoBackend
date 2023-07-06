<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FIRSTSHOW\Mpanel\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_scopeConfig;

	protected $_storeManager;

	protected $_date;

	protected $_url;

	protected $_wishlistItem;

	/**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

	protected $_categoryFactory;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Widget\Helper\Conditions $conditionsHelper,
		\Magento\Framework\Url $url,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Wishlist\Block\AbstractBlock $wishlistItem
	) {
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_date = $date;
		$this->_url = $url;
		$this->_wishlistItem = $wishlistItem;
		$this->conditionsHelper = $conditionsHelper;
		$this->_categoryFactory = $categoryFactory;
	}

	public function getStore(){
		return $this->_storeManager->getStore();
	}

	public function getStoreConfig($node, $storeId = NULL){
		if($storeId != NULL){
			return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}

	public function getThemeSettings(){
		return [
			'catalog'=>
			[
				'per_row' => $this->getStoreConfig('mpanel/catalog/product_per_row'),
				'featured' => $this->getStoreConfig('mpanel/catalog/featured'),
				'hot' => $this->getStoreConfig('mpanel/catalog/hot'),
				'ratio' => $this->getStoreConfig('mpanel/catalog/picture_ratio'),
				'new_label' => $this->getStoreConfig('mpanel/catalog/new_label'),
				'sale_label' => $this->getStoreConfig('mpanel/catalog/sale_label'),
				'preload' => $this->getStoreConfig('mpanel/catalog/preload'),
				'wishlist_button' => $this->getStoreConfig('mpanel/catalog/wishlist_button'),
				'compare_button' => $this->getStoreConfig('mpanel/catalog/compare_button')
			],
			'product_details'=>
			[
				'sku' => $this->getStoreConfig('mpanel/product_details/sku'),
				'reviews_summary' => $this->getStoreConfig('mpanel/product_details/reviews_summary'),
				'wishlist' => $this->getStoreConfig('mpanel/product_details/wishlist'),
				'compare' => $this->getStoreConfig('mpanel/product_details/compare'),
				'preload' => $this->getStoreConfig('mpanel/product_details/preload'),
				'short_description' => $this->getStoreConfig('mpanel/product_details/short_description'),
				'upsell_products' => $this->getStoreConfig('mpanel/product_details/upsell_products')
			],
			'product_tabs'=>
			[
				'show_description' => $this->getStoreConfig('mpanel/product_tabs/show_description'),
				'show_additional' => $this->getStoreConfig('mpanel/product_tabs/show_additional'),
				'show_reviews' => $this->getStoreConfig('mpanel/product_tabs/show_reviews'),
				'show_product_tag_list' => $this->getStoreConfig('mpanel/product_tabs/show_product_tag_list')
			],
			'contact_google_map'=>
			[
				'display_google_map' => $this->getStoreConfig('mpanel/contact_google_map/display_google_map'),
				'address_google_map' => $this->getStoreConfig('mpanel/contact_google_map/address_google_map'),
				'html_google_map' => $this->getStoreConfig('mpanel/contact_google_map/html_google_map'),
				'pin_google_map' => $this->getStoreConfig('mpanel/contact_google_map/pin_google_map')
			],
			'banner_slider'=>
			[
				'slider_tyle' => $this->getStoreConfig('firstshowtheme/banner_slider/slider_tyle'),
				'id_reslider' => $this->getStoreConfig('firstshowtheme/banner_slider/id_reslider'),
				'identifier_block' => $this->getStoreConfig('firstshowtheme/banner_slider/identifier_block'),
				'banner_owl_auto' => $this->getStoreConfig('firstshowtheme/banner_slider/banner_owl_auto'),
				'banner_owl_speed' => $this->getStoreConfig('firstshowtheme/banner_slider/banner_owl_speed'),
				'banner_owl_loop' => $this->getStoreConfig('firstshowtheme/banner_slider/banner_owl_loop'),
				'banner_owl_nav' => $this->getStoreConfig('firstshowtheme/banner_slider/banner_owl_nav'),
				'banner_owl_dot' => $this->getStoreConfig('firstshowtheme/banner_slider/banner_owl_dot')
			]
		];
	}

	/* Get col for responsive */
	public function getColClass($perrow = NULL){
		if(!$perrow){
			$settings = $this->getThemeSettings();
			$perrow = $settings['catalog']['per_row'];
		}

		switch($perrow){
			case 2:
				return 'col-lg-6 col-md-6 col-sm-6 col-xs-6';
				break;
			case 3:
				return 'col-lg-4 col-md-4 col-sm-4 col-xs-6';
				break;
			case 4:
				return 'col-lg-3 col-md-3 col-sm-6 col-xs-6';
				break;
			case 5:
				return 'col-md-custom-5 col-sm-4 col-xs-6';
				break;
			case 6:
				return 'col-lg-2 col-md-2 col-sm-3 col-xs-6';
				break;
		}
		return;
	}
	/* Get class clear left */
	public function getClearClass($perrow = NULL, $nb_item){
		if(!$perrow){
			$settings = $this->getThemeSettings();
			$perrow = $settings['catalog']['per_row'];
		}
		$clearClass = '';
		switch($perrow){
			case 2:
				if($nb_item % 2 == 1){
					$clearClass.= " first-row-item row-sm-first row-xs-first";
				}
				return $clearClass;
				break;
			case 3:
				if($nb_item % 3 == 1){
					$clearClass.= " first-row-item row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
			case 4:
				if($nb_item % 4 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-sm-first row-xs-first";
				}
				return $clearClass;
				break;
			case 6:
				if($nb_item % 6 == 1){
					$clearClass.= " first-row-item";
				}
				if($nb_item % 4 == 1){
					$clearClass.= " row-sm-first";
				}
				if($nb_item % 2 == 1){
					$clearClass.= " row-xs-first";
				}
				return $clearClass;
				break;
		}
		return $clearClass;
	}

	/* Get product image size */
	public function getImageSize(){
		$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image');
		if (stristr($maxWidth, ',')) {
			$sizeArr = explode(",", $maxWidth);
			return $result = array('width' => $sizeArr[0], 'height' => $sizeArr[1]);
		}
		$result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
	}

	/* Get product image size for product details page*/
	public function getImageSizeForDetails() {
		$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image_detail');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
    }

	public function getImageMinSize() {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => 80, 'height' => 80);
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => 80, 'height' => 160);
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => 80, 'height' => 120);
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => 80, 'height' => 107);
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => 80, 'height' => 40);
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => 80, 'height' => 53);
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => 80, 'height' => 60);
                break;
        }

        return $result;
    }

	public function getProductLabel($product){
		$html = '';
		$newLabel = $this->getStoreConfig('mpanel/catalog/new_label');
        $saleLabel = $this->getStoreConfig('mpanel/catalog/sale_label');

		// Sale label
		$price = $product->getPrice();
		$finalPrice = $product->getFinalPrice();
		if(($finalPrice<$price) && ($saleLabel!='')){
			$html .= '<span class="product-label sale-label"><span>'.$saleLabel.'</span></span>';
		}

		// New label
		$now = $this->_date->gmtDate();
		$dateTimeFormat = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
		$newFromDate = $product->getNewsFromDate();
        $newFromDate = date($dateTimeFormat, strtotime($newFromDate));
        $newToDate = $product->getNewsToDate();
        $newToDate = date($dateTimeFormat, strtotime($newToDate));
		if ((!(empty($newToDate) && empty($newFromDate)) && ($newFromDate < $now || empty($newFromDate)) && ($newToDate > $now || empty($newToDate)) && ($newLabel != '')) || ((empty($newToDate) && ($newFromDate < $now)) && ($newLabel != ''))) {
			$html.='<span class="product-label new-label"><span>'.$newLabel.'</span></span>';
		}

		return $html;
	}

	//Check if product is in wishlist
	public function checkInWishilist($_product){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->get('Magento\Customer\Model\Session');
		if($customerSession->isLoggedIn()){
			$wishlist = $this->_wishlistItem->getWishlistItems();
			if(count($wishlist) > 0){
				foreach($wishlist as $wishlistItem){
					$_item = $wishlistItem->getProduct()->getId();
					if($_item ==  $_product){
						$count_item = 1;
						break;
					}else {
						$count_item = 0;
					}
				}
				if($count_item == 1){
					return true;
				}else{
					return false;
				}
			}else {
				return false;
			}
		}else {
			return false;
		}
	}

	public function getUrlBuilder(){
		return $this->_url;
	}

	public function getCssUrl(){
		return $this->_url->getUrl('mpanel/index/css',['store'=>$this->getStore()->getId()]);
	}

	public function getFonts() {
        return [
            ['css-name' => 'Lato', 'font-name' => __('Lato')],
            ['css-name' => 'Open+Sans', 'font-name' => __('Open Sans')],
            ['css-name' => 'Roboto', 'font-name' => __('Roboto')],
            ['css-name' => 'Roboto Slab', 'font-name' => __('Roboto Slab')],
            ['css-name' => 'Oswald', 'font-name' => __('Oswald')],
            ['css-name' => 'Source+Sans+Pro', 'font-name' => __('Source Sans Pro')],
            ['css-name' => 'PT+Sans', 'font-name' => __('PT Sans')],
            ['css-name' => 'PT+Serif', 'font-name' => __('PT Serif')],
            ['css-name' => 'Droid+Serif', 'font-name' => __('Droid Serif')],
            ['css-name' => 'Josefin+Slab', 'font-name' => __('Josefin Slab')],
            ['css-name' => 'Montserrat', 'font-name' => __('Montserrat')],
            ['css-name' => 'Ubuntu', 'font-name' => __('Ubuntu')],
            ['css-name' => 'Titillium+Web', 'font-name' => __('Titillium Web')],
            ['css-name' => 'Noto+Sans', 'font-name' => __('Noto Sans')],
            ['css-name' => 'Lora', 'font-name' => __('Lora')],
            ['css-name' => 'Playfair+Display', 'font-name' => __('Playfair Display')],
            ['css-name' => 'Bree+Serif', 'font-name' => __('Bree Serif')],
            ['css-name' => 'Vollkorn', 'font-name' => __('Vollkorn')],
            ['css-name' => 'Alegreya', 'font-name' => __('Alegreya')],
            ['css-name' => 'Noto+Serif', 'font-name' => __('Noto Serif')],
            ['css-name' => 'Cuprum', 'font-name' => __('Cuprum')]
        ];
    }

	public function getLinksFont() {
        $setting = [
			'default_font' => $this->getStoreConfig('firstshowtheme/fonts/default_font'),
			'h1' => $this->getStoreConfig('firstshowtheme/fonts/h1'),
			'h2' => $this->getStoreConfig('firstshowtheme/fonts/h2'),
			'h3' => $this->getStoreConfig('firstshowtheme/fonts/h3'),
			'h4' => $this->getStoreConfig('firstshowtheme/fonts/h4'),
			'h5' => $this->getStoreConfig('firstshowtheme/fonts/h5'),
			'h6' => $this->getStoreConfig('firstshowtheme/fonts/h6'),
			'price' => $this->getStoreConfig('firstshowtheme/fonts/price'),
			'menu' => $this->getStoreConfig('firstshowtheme/fonts/menu'),
		];
        $fonts = [];
        $fonts[] = $setting['default_font'];

        if (!in_array($setting['h1'], $fonts)) {
            $fonts[] = $setting['h1'];
        }

        if (!in_array($setting['h2'], $fonts)) {
            $fonts[] = $setting['h2'];
        }

        if (!in_array($setting['h3'], $fonts)) {
            $fonts[] = $setting['h3'];
        }

        if (!in_array($setting['price'], $fonts)) {
            $fonts[] = $setting['price'];
        }

        if (!in_array($setting['menu'], $fonts)) {
            $fonts[] = $setting['menu'];
        }

        $fonts = array_filter($fonts);
        $links = '';

        foreach ($fonts as $_font) {
			$links .= '<link href="//fonts.googleapis.com/css?family=' . $_font . ':400,300,300italic,400italic,700,700italic,900,900italic" rel="stylesheet" type="text/css"/>';
        }

        return $links;
    }

	// get theme color
    public function getThemecolorSetting($storeId) {
        $setting = [
		/* Default Color */
			'.tab-electronic > li > a > span[class^="pe-7s"],.header-v5 .social li a:hover,.widget-latest-post .item .latest-post-decs .post-info a:hover,.widget-latest-post .item .latest-post-decs .latest-name a:hover,.widget-latest-post .item .read-more:hover,.service-box a:hover,.service-box .icon,.tab-menu.tab-products.tabs-categories-portfolio li a.is-checked,.shopping-guides li .icon,.chart-parent span,.breadcrumbs .breadcrumb li a:hover,.products-grid .item .product-content .product-desc .desc-prd .add-cart-item .product-item-actions button,.products-grid .item .product-content .product-desc .top-desc .controls .actions-secondary li a .fa-heart.active,.products-grid .item .product-content .product-desc .top-desc .controls .actions-secondary li a:hover,.category-products-list .item .controls .icon-links li a.action:hover,.toolbar .view-mode strong,.modal-popup._inner-scroll .modal-footer .action-hide-popup,.contact-info .icon,.catalog-product-compare-index .product-item-actions .action.tocart,.form-wishlist-items .product-item-actions a,.account-nav ul li.active a strong,.account-nav ul li.current a strong,.account-nav ul li.active strong,.account-nav ul li.current strong,.account-nav ul li a:hover,.account-nav ul li strong:hover,.account-nav ul li a:focus,.account-nav ul li strong:focus,.checkout-onepage-success .checkout-success .actions-toolbar .continue,.checkout-index-index .opc-sidebar .minicart-items .product-item .product-item-details .subtotal,.checkout-index-index .opc-sidebar .modal-content .opc-block-summary .grandtotal,.checkout-index-index .opc-wrapper .step-content .shipping-address-item .edit-address-link,.checkout-index-index .authentication-wrapper button span,.checkout-index-index .authentication-wrapper .secondary a,.checkout-index-index .checkout-payment-method .payment-option-title .action-toggle span,.checkout-cart-index .items .item .actions-toolbar a:hover,.catalog-product-view .product-essential .product-social-links .product-addto-links a.action:hover,.catalog-product-view .product-essential .product-info-stock-sku .product-info .text-desc a,.catalog-product-view .block.related .block-actions .action,.price,.service-inline .icon,.category-tabs .item.title.active a,.category-tabs .item.title a:hover,.vertical-menu li a:hover,.vertical-menu-home .vertical-menu-content .vertical-menu li.active > a,.header-v1 .navigation.mega-menu ul.nav-main li.level0.active > a.level0,.header-v3 .navigation.mega-menu ul.nav-main li.level0.active > a.level0,.header .top-header-content.header-v1 .header-social ul li a:hover,.block-search .btn-primary:focus,.block-search .btn-primary:active,.block-search .btn-primary:hover,.minicart-wrapper > .ui-widget-content .block-minicart #minicart-content-wrapper .block-content .minicart-items-wrapper .minicart-items > .item .product .product-item-details .actions a,.minicart-wrapper > .ui-widget-content .block-minicart #minicart-content-wrapper .block-content .minicart-items-wrapper .minicart-items > .item .product .product-item-details .actions,.minicart-wrapper > .ui-widget-content .block-minicart #minicart-content-wrapper .block-content .minicart-items-wrapper .minicart-items > .item .product .product-item-details .product-name a:hover,.switcher-language .switcher-options > .ui-widget-content ul.switcher-dropdown li a:hover,.switcher-currency .switcher-options > .ui-widget-content ul.switcher-dropdown li a:hover,.switcher-language .dropdown span:hover,.switcher-currency .dropdown span:hover,a:hover, a:focus' => [
                'color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
			'.tab-electronic > li.active,.tab-electronic > li:hover,.header-v5 .vertical-menu-home .vertical-menu-content .vertical-menu > li,.slider_firstshow_carousel .owl-dots .owl-dot.active span,.slider_firstshow_carousel .owl-dots .owl-dot:hover span,.tab-center .btn-promo-banner:hover,.products-grid .item .product-content .product-desc .desc-prd .add-cart-item .product-item-actions button:hover,.category-products-list .item .controls .icon-links li.add-cart-item button,.toolbar .top-toolbar .pager .sort-by .sorter-action .fa.sellect,.modal-popup .modal-footer .action-primary,.modal-popup._inner-scroll .modal-footer .action-save-address,.footer .top-footer,.checkout-index-index .checkout-payment-method .payment-method-content .actions-toolbar button,.checkout-cart-index .shopping-cart-bottom .totals .table-caption,.checkout-cart-index .shopping-cart-bottom .title,.vertical-menu-home .vertical-title,.minicart-wrapper .showcart .counter' => [
                'background-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
			'.block-blog-tags .block-content .small:hover,.tab-center .btn-promo-banner:hover,.toolbar .top-toolbar .pager .sort-by .sorter-action .fa.sellect,.modal-popup .modal-footer .action-primary,.modal-popup._inner-scroll .modal-footer .action-save-address,.checkout-index-index .methods-shipping .actions-toolbar button,.checkout-index-index .opc-wrapper .form-login button.login,.checkout-index-index .opc-wrapper .step-content .action-show-popup,.checkout-index-index .opc-wrapper .step-content .shipping-address-item.not-selected-item button,.checkout-index-index .checkout-payment-method .payment-option-content .actions-toolbar button:hover,.checkout-index-index .checkout-payment-method .payment-method-content .actions-toolbar button,.checkout-cart-index .shopping-cart-bottom .totals tbody,.checkout-cart-index .shopping-cart-bottom .content,.catalog-product-view .info.detailed .detailed-menu .item.title.active a,.catalog-product-view .info.detailed .detailed-menu .content' => [
                'border-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
			'.accordion_question.panel-group .panel-collapse .panel-body,.vertical-menu-home .vertical-title' => [
                'border-top-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
			'.catalog-product-view .info.detailed .detailed-menu .item.title a:hover' => [
                'border-bottom-color' => $this->getStoreConfig('color/general/theme_color', $storeId)
            ],
		];
        $setting = array_filter($setting);
        return $setting;
    }

	// get header custom color
    public function getHeaderColorSetting($storeId) {
        $setting = [
            /* Header Top Section */
            '.header .top-header-content' => [
                'background-color' => $this->getStoreConfig('color/header/background_color', $storeId),
                'color' => $this->getStoreConfig('color/header/text_color', $storeId)
            ],
			'.header .top-header-content .dropdown .action' => [
                'color' => $this->getStoreConfig('color/header/text_color', $storeId)
            ],
			'.header .top-header-content a,.header .top-header-content .top-bar-right .switcher,.switcher-language .dropdown span, .switcher-currency .dropdown span,.header .top-header-content .top-bar-right .header-newsletter,.block-search .name' => [
                'color' => $this->getStoreConfig('color/header/link_color', $storeId)
            ],
			'.header .top-header-content a:hover,.header .top-header-content .top-bar-right .switcher:hover,.switcher-language .dropdown:hover span, .switcher-currency .dropdown:hover span,.header .top-header-content .top-bar-right .header-newsletter:hover,.block-search .name:hover' => [
                'color' => $this->getStoreConfig('color/header/link_hover_color', $storeId)
            ],
			'.header .top-header-content .dropdown .ui-dialog' => [
                'background-color' => $this->getStoreConfig('color/header/dropdown_background', $storeId)
            ],
			'.header .top-header-content .switcher .switcher-options .ui-widget-content ul.switcher-dropdown li a' => [
                'color' => $this->getStoreConfig('color/header/dropdown_link_color', $storeId)
            ],
			'.header .top-header-content .switcher .switcher-options .ui-widget-content ul.switcher-dropdown li a:hover' => [
                'color' => $this->getStoreConfig('color/header/dropdown_link_hover_color', $storeId)
            ],
			/* Header Middle Section */
			'.header .middle-header-content' => [
                'background-color' => $this->getStoreConfig('color/header/middle_background', $storeId)
            ],
			/* Top Search Section */
			'.header #search_mini_form .input-text' => [
                'background-color' => $this->getStoreConfig('color/header/search_input_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_input_border', $storeId),
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId),
            ],
			'.header #search_mini_form .input-text::-webkit-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text:-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text::-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text:-ms-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .btn-primary' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text', $storeId)
            ],
			'.header #search_mini_form .btn-primary:hover' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text_hover', $storeId)
            ],
			/* Top Cart Section */
			'.header .minicart-wrapper .text .fa' => [
                'color' => $this->getStoreConfig('color/header/cart_icon', $storeId)
            ],
			'.header .minicart-wrapper .counter .counter-number' => [
                'background-color' => $this->getStoreConfig('color/header/cart_number_background', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_number', $storeId)
            ],
			'.header .minicart-wrapper > .ui-widget-content' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_border', $storeId),
            ],
			'.header .minicart-wrapper .ui-widget-content .block-content, .minicart-wrapper .ui-widget-content .block-content .subtitle' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_text', $storeId)
            ],
			'.header .minicart-wrapper .ui-widget-content .block-content a' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_link', $storeId)
            ],
			'.header .minicart-wrapper .ui-widget-content .block-content a:hover' => [
                'color' => $this->getStoreConfig('color/header/cart_dropdown_link_hover', $storeId)
            ],
			'.header .minicart-wrapper .ui-widget-content button, .minicart-wrapper .ui-widget-content .btn' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_dropdown_button_text', $storeId),
            ],
			'.header .minicart-wrapper .ui-widget-content button:hover, .minicart-wrapper .ui-widget-content .btn:hover' => [
                'background-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/cart_dropdown_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/cart_dropdown_button_text_hover', $storeId),
            ],
			/* Top Search Section */
			'.header #search_mini_form .input-text' => [
                'background-color' => $this->getStoreConfig('color/header/search_input_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_input_border', $storeId),
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId),
            ],
			'.header #search_mini_form .input-text::-webkit-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text:-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text::-moz-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .input-text:-ms-input-placeholder' => [
                'color' => $this->getStoreConfig('color/header/search_input_text', $storeId)
            ],
			'.header #search_mini_form .btn-primary' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text', $storeId)
            ],
			'.header #search_mini_form .btn-primary:hover' => [
                'background-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/header/search_button_background_hover', $storeId),
                'color' => $this->getStoreConfig('color/header/search_button_text_hover', $storeId)
            ],
			/* Menu Section */
			'.header nav.navigation,.header .navbar-collapse' => [
                'background-color' => $this->getStoreConfig('color/header/menu_background', $storeId)
            ],
			'.header nav.navigation #mainMenu > .level0' => [
                'background-color' => $this->getStoreConfig('color/header/lv1_background', $storeId)
            ],
			'.header #mainMenu .level0 a.level0' => [
                'color' => $this->getStoreConfig('color/header/lv1_color', $storeId)
            ],
			'.header nav.navigation #mainMenu > .level0:hover' => [
                'background-color' => $this->getStoreConfig('color/header/lv1_background_hover', $storeId)
            ],
			'nav.navigation #mainMenu li:hover a.level0, nav.navigation #mainMenu a.level0:active, nav.navigation #mainMenu a.level0:focus, nav.navigation #mainMenu .level0 > a.ui-state-focus, nav.navigation #mainMenu li.active a.level0' => [
                'color' => $this->getStoreConfig('color/header/lv1_color_hover', $storeId)
            ],
			'.header nav.navigation li.level0 ul.dropdown-menu,.header  nav.navigation #mainMenu .level0 ul.level0,.header  nav.navigation #mainMenu .level0 ul.level0 li.level1 ul.level1' => [
                'background-color' => $this->getStoreConfig('color/header/menu_dropdown_background', $storeId)
            ],
			'.header nav.navigation li.level0 ul.dropdown-menu li a' => [
                'color' => $this->getStoreConfig('color/header/menu_dropdown_link_color', $storeId)
            ],
			'.header nav.navigation li.level0 ul.dropdown-menu:hover' => [
                'background-color' => $this->getStoreConfig('color/header/menu_dropdown_background_hover', $storeId)
            ],
			'.navigation ul.nav-main li.level0 li.level1 a:hover, .navigation ul.nav-main li.level0 li a:hover' => [
                'color' => $this->getStoreConfig('color/header/menu_dropdown_link_color_hover', $storeId)
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }

	// get main content custom color
    public function getMainColorSetting($storeId) {
        $setting = [
            /* Text & Link color */
            '.page-main' => [
                'color' => $this->getStoreConfig('color/main/text_color', $storeId)
            ],
			'.page-main a' => [
                'color' => $this->getStoreConfig('color/main/link_color', $storeId)
            ],
			'.page-main a:hover' => [
                'color' => $this->getStoreConfig('color/main/link_color_hover', $storeId)
            ],
			'.page-main .price, .page-main .price-box .price' => [
                'color' => $this->getStoreConfig('color/main/price_color', $storeId)
            ],
			/* Default button color */
            'button, button.btn, button.btn-default' => [
                'color' => $this->getStoreConfig('color/main/button_text', $storeId),
                'background-color' => $this->getStoreConfig('color/main/button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/button_border', $storeId)
            ],
			'button:hover, button.btn:hover, button.btn-default:hover' => [
                'color' => $this->getStoreConfig('color/main/button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/button_border_hover', $storeId)
            ],
			/* Primary button color */
            'button.btn-primary' => [
                'color' => $this->getStoreConfig('color/main/primary_button_text'),
                'background-color' => $this->getStoreConfig('color/main/primary_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/primary_button_border', $storeId)
            ],
			'button.btn-primary:hover' => [
                'color' => $this->getStoreConfig('color/main/primary_button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/primary_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/primary_button_border_hover', $storeId)
            ],
			/* Secondary button color */
            'button.btn-secondary' => [
                'color' => $this->getStoreConfig('color/main/secondary_button_text', $storeId),
                'background-color' => $this->getStoreConfig('color/main/secondary_button_background', $storeId),
                'border-color' => $this->getStoreConfig('color/main/secondary_button_border', $storeId)
            ],
			'button.btn-secondary:hover' => [
                'color' => $this->getStoreConfig('color/main/secondary_button_text_hover', $storeId),
                'background-color' => $this->getStoreConfig('color/main/secondary_button_background_hover', $storeId),
                'border-color' => $this->getStoreConfig('color/main/secondary_button_border_hover', $storeId)
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }

	// get main content custom color
    public function getFooterColorSetting($storeId) {
        $setting = [
            /* Top Footer Section */
            'footer .top-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/top_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/top_text_color', $storeId),
                'border-color' => $this->getStoreConfig('color/footer/top_border_color', $storeId)
            ],
			'footer .top-footer label' => [
                'color' => $this->getStoreConfig('color/footer/top_text_color', $storeId)
            ],
			'footer .top-footer h1,footer .top-footer h2,footer .top-footer h3,footer .top-footer h4,footer .top-footer h5,footer .top-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/top_heading_color', $storeId),
            ],
			'footer .top-footer a' => [
                'color' => $this->getStoreConfig('color/footer/top_link_color', $storeId),
            ],
			'footer .top-footer a:hover' => [
                'color' => $this->getStoreConfig('color/footer/top_link_color_hover', $storeId),
            ],
			'footer .top-footer .fa' => [
                'color' => $this->getStoreConfig('color/footer/top_icon_color', $storeId),
            ],
			/* Middle Footer Section */
            'footer .middle-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/middle_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/middle_text_color', $storeId),
                'border-color' => $this->getStoreConfig('color/footer/middle_border_color', $storeId)
            ],
			'footer .middle-footer label' => [
                'color' => $this->getStoreConfig('color/footer/middle_text_color', $storeId)
            ],
			'footer .middle-footer h1,footer .middle-footer h2,footer .middle-footer h3,footer .middle-footer h4,footer .middle-footer h5,footer .middle-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/middle_heading_color', $storeId),
            ],
			'footer .middle-footer a' => [
                'color' => $this->getStoreConfig('color/footer/middle_link_color', $storeId),
            ],
			'footer .middle-footer a:hover' => [
                'color' => $this->getStoreConfig('color/footer/middle_link_color_hover', $storeId),
            ],
			'footer .middle-footer .fa' => [
                'color' => $this->getStoreConfig('color/footer/middle_icon_color', $storeId),
            ],
			/* Bottom Footer Section */
            'footer .bottom-footer' => [
                'background-color' => $this->getStoreConfig('color/footer/bottom_background_color', $storeId),
                'color' => $this->getStoreConfig('color/footer/bottom_text_color', $storeId),
                'border-color' => $this->getStoreConfig('color/footer/bottom_border_color', $storeId)
            ],
			'footer .bottom-footer label' => [
                'color' => $this->getStoreConfig('color/footer/bottom_text_color', $storeId)
            ],
			'footer .bottom-footer h1,footer .bottom-footer h2,footer .bottom-footer h3,footer .bottom-footer h4,footer .bottom-footer h5,footer .bottom-footer h6' => [
                'color' => $this->getStoreConfig('color/footer/bottom_heading_color', $storeId),
            ],
			'footer .bottom-footer a' => [
                'color' => $this->getStoreConfig('color/footer/bottom_link_color', $storeId),
            ],
			'footer .bottom-footer a:hover' => [
                'color' => $this->getStoreConfig('color/footer/bottom_link_color_hover', $storeId),
            ],
			'footer .bottom-footer .fa' => [
                'color' => $this->getStoreConfig('color/footer/bottom_icon_color', $storeId),
            ],
        ];
        $setting = array_filter($setting);
        return $setting;
    }
	public function getConditions($conditions)
    {
        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }
        return $conditions;
    }
	public function getCategory($categoryId)
	{
		$category = $this->_categoryFactory->create();
		$category->load($categoryId);
		return $category;
	}

	public function getCategoryProducts($categoryId)
	{
		$products = $this->getCategory($categoryId)->getProductCollection();
		$products->addAttributeToSelect('*');
		return $products;
	}
}
