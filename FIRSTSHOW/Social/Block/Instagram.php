<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Social\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Instagram extends Template 
{
	
	public function _iscurl(){
		if(function_exists('curl_version')) {
			return true;
		} else {
			return false;
		}
	}	
	
	public function getInstagramData($access_token=NULL, $width=NULL, $height=NULL, $number=NULL) {
		$host = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$access_token;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, true);
		//print_r($content); exit();
		$j = 0;
		$i = 0;
		if(isset($content['data'])) {
			foreach($content['data'] as $contents){
				$j++;
			}
		}
		if(!(isset($content['data'][$i]['images']['low_resolution']['url'])) || !$content['data'][$i]['images']['low_resolution']['url']) {
			echo 'There are not any images in this instagram.';
			return false;
		}
		if(!$width){
			$width = 100;
		}
		if(!$height){
			$height = 100;
		}
		if($number > $j) {
			for($i=0 ; $i<$j; $i++){
				$html = "<a href='".$content['data'][$i]['images']['low_resolution']['url']."' rel='nofollow' target='_blank'><img width='".$width."' height='".$height."' src='".$content['data'][$i]['images']['low_resolution']['url']."' alt='' /></a>";
				echo $html;
			}
		} else {
			for($i=0 ; $i<$number; $i++){
				$html = "<a href='".$content['data'][$i]['images']['low_resolution']['url']."' rel='nofollow' target='_blank'><img width='".$width."' height='".$height."' src='".$content['data'][$i]['images']['low_resolution']['url']."' alt='' /></a>";
				echo $html;
			}
		}
		
	}
	
	public function getWidgetInstagramData($access_token=NULL, $width=NULL, $height=NULL, $number=NULL) {
		$host = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$access_token;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, true);
		$j = 0;
		$i = 0;
		if(isset($content['data'])) {
			foreach($content['data'] as $contents){
				$j++;
			}
		}
		if(!(isset($content['data'][$i]['images']['low_resolution']['url'])) || !$content['data'][$i]['images']['low_resolution']['url']) {
			echo 'There are not any images in this instagram.';
			return false;
		}
		$images = array();
		if($number > $j) {
			for($i=0 ; $i<$j; $i++){
				$images[$i] = $content['data'][$i]['images']['low_resolution']['url'];
			}
		} else {
			for($i=0 ; $i<$number; $i++){
				$images[$i] = $content['data'][$i]['images']['low_resolution']['url'];
			}
		}
		return $images;
	}
}