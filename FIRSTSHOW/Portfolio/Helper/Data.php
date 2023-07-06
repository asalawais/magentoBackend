<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FIRSTSHOW\Portfolio\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/* Get col for responsive */
	public function getColClass($perrow){
		switch($perrow){
			case 2:
				return 'col-lg-6 col-md-6 col-sm-6 col-xs-12';
				break;
			case 3:
				return 'col-lg-4 col-md-4 col-sm-4 col-xs-12';
				break;
			case 4:
				return 'col-lg-3 col-md-3 col-sm-6 col-xs-12';
				break;
			case 6:
				return 'col-lg-2 col-md-2 col-sm-2 col-xs-12';
				break;
		}
		return;
	}
}