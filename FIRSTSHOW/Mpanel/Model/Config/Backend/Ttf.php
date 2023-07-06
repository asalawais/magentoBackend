<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config Logo image field backend model
 */
namespace FIRSTSHOW\Mpanel\Model\Config\Backend;

class Ttf extends \FIRSTSHOW\Mpanel\Model\Config\Backend\Font
{
    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['ttf'];
    }
}
