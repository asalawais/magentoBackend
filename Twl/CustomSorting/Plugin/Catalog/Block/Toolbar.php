<?php


namespace Twl\CustomSorting\Plugin\Catalog\Block;


class Toolbar

{


  public function aroundSetCollection( \Magento\ Catalog\ Block\ Product\ ProductList\ Toolbar $subject, \Closure $proceed, $collection ) {
    $currentOrder = $subject->getCurrentOrder();
    $result = $proceed( $collection );
    if ( $currentOrder ) {
      if ( $currentOrder == 'high_to_low' ) {
        $subject->getCollection()->setOrder( 'price', 'desc' );
      } elseif ( $currentOrder == 'low_to_high' ) {
        $subject->getCollection()->setOrder( 'price', 'asc' );
      } elseif ($currentOrder == "newest_product"){
		  $direction = $subject->getCurrentDirection();
		  $subject->getCollection()->setOrder( 'created_at', 'desc' );
	  }
    } else {
      $subject->getCollection()->getSelect()->reset( 'order' );
      $subject->getCollection()->setOrder( 'price', 'asc' );
    }
    return $result;
  }


}