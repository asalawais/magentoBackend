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

namespace RLTSquare\ProductImagesSlider\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * Class Product
 * @package RLTSquare\ProductImagesSlider\Block
 */
class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Product constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        Template\Context $context,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->request->getParam('id');
    }

    /**
     * @param $id
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById($id): ProductInterface
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @return int
     */
    public function isEnable()
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/isEnableDisable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imageSlider():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imageSlider', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imageBreakpoint1():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imageBreakpoint1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imageBreakpoint2():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imageBreakpoint2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imageBreakpoint3():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imageBreakpoint3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imageBreakpoint4():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imageBreakpoint4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imagePopupSlider():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imagePopupSlider', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imagePopupBreakpoint1():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imagePopupBreakpoint1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imagePopupBreakpoint2(): string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imagePopupBreakpoint2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imagePopupBreakpoint3():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imagePopupBreakpoint3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function imagePopupBreakpoint4():string
    {
        return $this->scopeConfig->getValue('productImages/productImagesGroup/imagePopupBreakpoint4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
