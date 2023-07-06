<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace BigBridge\ProductImport\Cron;

/**
 * Interface CronJobInterface
 */
interface CronJobInterface
{
    /**
     * @return void
     */
    public function execute();
}
