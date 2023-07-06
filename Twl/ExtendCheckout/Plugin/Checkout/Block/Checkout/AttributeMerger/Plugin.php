<?php

namespace Twl\ExtendCheckout\Plugin\Checkout\Block\Checkout\AttributeMerger;


class Plugin
{

  public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)
  {
  	//echo '<pre>'; var_dump($result); exit;
    if (array_key_exists('street', $result)) {
	  $result['street']['label'] = __('Location:');
      $result['street']['children'][0]['placeholder'] = __('Address Line 1 *');
      $result['street']['children'][1]['placeholder'] = __('Address Line 2');
    }
	if (array_key_exists('firstname', $result)) {
      $result['firstname']['placeholder'] = __('First Name').' *';
    }
	
	if (array_key_exists('lastname', $result)) {
      $result['lastname']['placeholder'] = __('Last Name');
    }
	
	if (array_key_exists('city', $result)) {
      $result['city']['placeholder'] = __('City').' *';
	  $result['city']['config']['additionalClasses'] = 'shipping-city';
    }
	
	if (array_key_exists('region_id', $result)) {
      $result['region_id']['placeholder'] = __('State').' *';
	  $result['region_id']['config']['additionalClasses'] = 'shipping-state';
    }
	
	if (array_key_exists('region', $result)) {
      $result['region']['placeholder'] = __('State').' *';
	  $result['region']['config']['additionalClasses'] = 'shipping-state';
    }
	
	if (array_key_exists('postcode', $result)) {
      $result['postcode']['placeholder'] = __('Postal Code').' *';
    }
	
	if (array_key_exists('telephone', $result)) {
      $result['telephone']['placeholder'] = __('Contact Number').' *';
    }

    return $result;
  }


}
