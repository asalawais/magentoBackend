<?php
/**
 * @package  BigBridge\ProductImport
 * @license See LICENSE.txt for license details.
 */

namespace BigBridge\ProductImport\System;

/**
 * Class ConfigInterface
 */
interface ConfigInterface
{
    /**
     * Configuration path for module status
     */
    const XML_PATH_MODULE_ENABLED = 'configuration/basic/is_enabled';

    /**
     * Configuration for monolog handler
     */
    const XML_PATH_LOGGER_TYPE = 'configuration/basic/logger_type';

    /**
     * Configuration for queue outdated value
     */
    const XML_PATH_QUEUE_OUTDATED = 'configuration/basic/queue_outdated';

    /**
     * Configuration path for Afas API Key used for request authorization
     */
    const XML_PATH_PIMCORE_API_KEY = 'bigbridge/integration/api_key';

    /**
     * Configuration path for Afas Endpoint
     */
    const XML_PATH_PIMCORE_ENDPOINT = 'bigbridge/integration/endpoint';

    /**
     * Configuration path for Afas Endpoint
     */
    const XML_PATH_INSTANCE_URL = 'bigbridge/integration/instance_url';

    /**
     * Configuration path for Category Queue Process
     */
    const XML_PATH_CAT_QUEUE_PROCESS = 'bigbridge/integration/category_queue_process';

    /**
     * Configuration path for Product Queue Process
     */
    const XML_PATH_PROD_QUEUE_PROCESS = 'bigbridge/integration/product_queue_process';

    /**
     * Configuration path for Asset Queue Process
     */
    const XML_PATH_ASSET_QUEUE_PROCESS = 'bigbridge/integration/asset_queue_process';

    /**
     * Configuration path for Asset Queue Process
     */
    const XML_PATH_CRON_PUBLISH_IS_ACTIVE = 'cron/enable_products/is_active';

    /**
     * @return bool
     */
    public function isConfigurationValid(): bool;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string|null
     */
    public function getAfasApiKey();

    /**
     * @return int
     */
    public function getLoggerType(): int;

    /**
     * @return string
     */
    public function getAfasEndpoint(): string;

    /**
     * @return int
     */
    public function getCategoryQueueProcess(): int;

    /**
     * @return int
     */
    public function getProductQueueProcess(): int;

    /**
     * @return int
     */
    public function getAssetQueueProcess(): int;

    /**
     * @return string
     */
    public function getInstanceUrl(): string;

    /**
     * @return string
     */
    public function getQueueOutdatedValue(): string;

    /**
     * @return bool
     */
    public function getIsProductPublishActive(): bool;
}
