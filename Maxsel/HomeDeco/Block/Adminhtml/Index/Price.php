<?php
/**
 * Copyright © Maxsel.nl All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Maxsel\HomeDeco\Block\Adminhtml\Index;
use Maxsel\Emesa\Helper\Connect;
use Psr\Log\LoggerInterface;

class Price extends \Magento\Backend\Block\Template
{

    /**
     * @var Connect
     */
    protected $connect;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Connect $connect,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->connect = $connect;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getPrices()
    {
        $data = [];
        if(is_array($this->connect->getShippingClasses()) > 0) {
            foreach ($this->connect->getShippingClasses() as $shippingClass) {
                $data[$shippingClass->getMarketShippingClassId()]['nl'] = $shippingClass->getCostsInCents()->getNl();
                $data[$shippingClass->getMarketShippingClassId()]['be'] = $shippingClass->getCostsInCents()->getBe();
                $data[$shippingClass->getMarketShippingClassId()]['de'] = $shippingClass->getCostsInCents()->getDe();
            }
        }
        return $data;
    }


}

