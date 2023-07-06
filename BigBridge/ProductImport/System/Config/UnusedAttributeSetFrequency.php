<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\System\Config;

/**
 * Class UnusedAttributeSetFrequency
 */
class UnusedAttributeSetFrequency extends AbstractFrequencyConfig
{
    /**
     * @return string
     */
    protected function getCronStringPath(): string
    {
        return 'crontab/divante_pimcore_integration/jobs/remove_unused_attribute_sets/schedule/cron_expr';
    }

    /**
     * @return string
     */
    protected function getCronModelPath(): string
    {
        return 'crontab/divante_pimcore_integration/jobs/remove_unused_attribute_sets/run/model';
    }

    /**
     * @return string
     */
    protected function getTimeConfigValuePath(): string
    {
        return 'groups/attribute_sets/fields/time/value';
    }

    /**
     * @return string
     */
    protected function getFrequencyConfigValuePath(): string
    {
        return 'groups/attribute_sets/fields/frequency/value';
    }
}
