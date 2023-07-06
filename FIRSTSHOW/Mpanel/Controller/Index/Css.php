<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FIRSTSHOW\Mpanel\Controller\Index;

class Css extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
		// $storeId = $this->getRequest()->getParam('store');
		// $helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('FIRSTSHOW\Mpanel\Helper\Data');
		// $html = '';
    //
		// $fontName = $helper->getStoreConfig('firstshowtheme/custom_style/font_name', $storeId);
		// if($fontName!=''){
		// 	$fontDir = $helper->getUrlBuilder()->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . \FIRSTSHOW\Mpanel\Model\Config\Backend\Font::UPLOAD_DIR.'/';
		// 	$ttfFile = $fontDir . $helper->getStoreConfig('firstshowtheme/custom_style/ttf_file', $storeId);
		// 	$eotFile = $fontDir . $helper->getStoreConfig('firstshowtheme/custom_style/eot_file', $storeId);
		// 	$woffFile = $fontDir . $helper->getStoreConfig('firstshowtheme/custom_style/woff_file', $storeId);
		// 	$svgFile = $fontDir . $helper->getStoreConfig('firstshowtheme/custom_style/svg_file', $storeId);
    //
		// 	if ($ttfFile != '' && $eotFile != '') {
		// 		$html .= '@font-face {
		// 				font-family: "' . $fontName . '";
		// 				src: url("' . $eotFile . '");
		// 				src: url("' . $eotFile . '?#iefix") format("embedded-opentype"),
		// 					 url("' . $woffFile . '") format("woff"),
		// 					 url("' . $ttfFile . '") format("truetype"),
		// 					 url("' . $svgFile . '#' . $fontName . '") format("svg");
		// 				font-weight: normal;
		// 				font-style: normal;
		// 		}';
		// 	}
    //
		// }
    //
		// $html .= 'body{';
		// $backgroundColor = $helper->getStoreConfig('firstshowtheme/background/background_color', $storeId);
		// $backgroundImage = $helper->getStoreConfig('firstshowtheme/background/background_image', $storeId);
		// if($backgroundColor!=''){
		// 	$html .= 'background-color:'.$backgroundColor.';';
		// }
		// if($backgroundImage!=''){
		// 	$folderName = \FIRSTSHOW\Mpanel\Model\Config\Backend\Image::UPLOAD_DIR;
    //
		// 	$path = $folderName . '/' . $backgroundImage;
		// 	$backgroundImageUrl = $helper->getUrlBuilder()->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
    //
		// 	$html .= 'background-image:url('.$backgroundImageUrl.');';
		// 	$backgroundCover = $helper->getStoreConfig('firstshowtheme/background/background_cover', $storeId);
		// 	if($backgroundCover){
		// 		$html.= 'background-size:cover;';
		// 	}else{
		// 		$backgroundRepeat = $helper->getStoreConfig('firstshowtheme/background/background_repeat', $storeId);
		// 		$html.= 'background-repeat:'.$backgroundRepeat.';';
		// 	}
		// 	$backgroundPositionX = $helper->getStoreConfig('firstshowtheme/background/background_position_x', $storeId);
		// 	$backgroundPositionY = $helper->getStoreConfig('firstshowtheme/background/background_position_y', $storeId);
		// 	$html.= 'background-position:'.$backgroundPositionX.' '.$backgroundPositionY.';';
		// }
    //
		// if($helper->getStoreConfig('firstshowtheme/fonts/default_font', $storeId)!=''){
		// 	$html .= 'font-family: "' . str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/default_font', $storeId)) . '", arial, tahoma;font-weight: normal;';
		// }
    //
		// $fontSize = $helper->getStoreConfig('firstshowtheme/fonts/default_font_size', $storeId);
		// if ($fontSize != '') {
		// 	$html .= 'font-size:' . $fontSize . ';';
		// }
    //
	  //  $html .= '}';
    //
		// $fontStyle = [
		// 	'.navigation li.level0 a.level-top, .navigation ul.container .level0 > a' => [
		// 		'font-family' => str_replace('+', ' ',$helper->getStoreConfig('firstshowtheme/fonts/menu', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/menu_font_size', $storeId),
		// 	],
		// 	'h1,.h1' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h1', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h1_font_size', $storeId),
		// 	],
		// 	'h2,.h2' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h2', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h2_font_size', $storeId),
		// 	],
		// 	'h3,.h3' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h3', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h3_font_size', $storeId),
		// 	],
		// 	'h4,.h4' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h4', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h4_font_size', $storeId),
		// 	],
		// 	'h5,.h5' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h5', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h5_font_size', $storeId),
		// 	],
		// 	'h6,.h6' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/h6', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/h6_font_size', $storeId),
		// 	],
		// 	'.btn' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/btn', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/btn_font_size', $storeId),
		// 	],
		// 	'.price, .price-box .price' => [
		// 		'font-family' => str_replace('+', ' ', $helper->getStoreConfig('firstshowtheme/fonts/price', $storeId)),
		// 		'font-size' => $helper->getStoreConfig('firstshowtheme/fonts/price_font_size', $storeId),
		// 	]
		// ];
    //
		// $fontStyle = array_filter($fontStyle);
    //
		// foreach ($fontStyle as $class => $style) {
		// 	$style = array_filter($style);
		// 	if (count($style) > 0) {
		// 		$html .= $class . '{';
		// 		foreach ($style as $_style => $value) {
		// 			if($_style=='font-family'){
		// 				$html .= $_style . ': "' . $value . '";';
		// 			}else{
		// 				$html .= $_style . ': ' . $value . ';';
		// 			}
		// 		}
		// 		$html .= '}
		// 		';
		// 	}
		// }
    //
		// if(($helper->getStoreConfig('color/general/theme_color', $storeId) != '') && ($helper->getStoreConfig('color/general/theme_color', $storeId) != 'transparent')){
		// 	$themeColorSetting = $helper->getThemecolorSetting($storeId);
		// 	if (count($themeColorSetting) > 0) {
		// 		foreach ($themeColorSetting as $class => $style) {
		// 			$style = array_filter($style);
		// 			if (count($style) > 0) {
		// 				$html .= $class . '{';
		// 				foreach ($style as $_style => $value) {
		// 					$html .= $_style . ': ' . $value . ';';
		// 				}
		// 				$html .= '}';
		// 			}
		// 		}
		// 	}
		// }
    //
		// if($helper->getStoreConfig('color/header/header_custom', $storeId)){
		// 	$headerColorSetting = $helper->getHeaderColorSetting($storeId);
		// 	if (count($headerColorSetting) > 0) {
		// 		foreach ($headerColorSetting as $class => $style) {
		// 			$style = array_filter($style);
		// 			if (count($style) > 0) {
		// 				$html .= $class . '{';
		// 				foreach ($style as $_style => $value) {
		// 					$html .= $_style . ': ' . $value . ';';
		// 				}
		// 				$html .= '}';
		// 			}
		// 		}
		// 	}
		// }
    //
		// if($helper->getStoreConfig('color/main/main_custom', $storeId)){
		// 	$mainColorSetting = $helper->getMainColorSetting($storeId);
		// 	if (count($mainColorSetting) > 0) {
		// 		foreach ($mainColorSetting as $class => $style) {
		// 			$style = array_filter($style);
		// 			if (count($style) > 0) {
		// 				$html .= $class . '{';
		// 				foreach ($style as $_style => $value) {
		// 					$html .= $_style . ': ' . $value . ';';
		// 				}
		// 				$html .= '}';
		// 			}
		// 		}
		// 	}
		// }
    //
		// if ($helper->getStoreConfig('firstshowtheme/custom_style/style', $storeId) != '') {
    //         $html .= $helper->getStoreConfig('firstshowtheme/custom_style/style', $storeId);
    //     }
    //
		// $this->getResponse()->setHeader('Content-type', 'text/css', true);
		// $this->getResponse()->setBody($html);
    }
}
