<?php
/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @package   RLTSquare_ProductImagesSlider
 * @copyright Copyright (c) 2022 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

namespace RLTSquare\ProductImagesSlider\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Class ImageSlider
 * @package RLTSquare\ProductImagesSlider\Model\Config\Source
 */
class ImageSlider implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '2', 'label' => __('2')],
            ['value' => '3', 'label' => __('3')]
        ];
    }
}
