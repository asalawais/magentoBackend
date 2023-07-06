<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Maxsel\CustomerCreation\Block\Swatches\Product\Renderer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributesProvider;

/**
 * Swatch renderer block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * Path to template file with Swatch renderer.
     */
    const SWATCH_RENDERER_TEMPLATE = 'Magento_Swatches::product/view/renderer.phtml';

    /**
     * Return renderer template
     *
     * Template for product with swatches is different from product without swatches
     *
     * @return string
     */

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var SwatchData
     */
    protected $swatchHelper;

    /**
     * @var Media
     */
    protected $swatchMediaHelper;

    /**
     * Indicate if product has one or more Swatch attributes
     *
     * @deprecated 100.1.0 unused
     *
     * @var boolean
     */
    protected $isProductHasSwatchAttribute;

    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributesProvider;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    protected $stockRegistry;

    protected $jsonDecoder;

    /** @var HideData */
    private $hideHelper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param UrlBuilder|null $imageUrlBuilder
     *
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null,
        DecoderInterface $jsonDecoder,
        ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->swatchAttributesProvider = $swatchAttributesProvider
            ?: ObjectManager::getInstance()->get(SwatchAttributesProvider::class);
        $this->imageUrlBuilder = $imageUrlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->jsonDecoder = $jsonDecoder;
        $this->stockRegistry = $stockRegistry;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data,
            $swatchAttributesProvider,
            $imageUrlBuilder
        );
    }

    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    public function getStockItem($productSku)
    {
        return $this->stockRegistry->getStockItemBySku($productSku);
    }

    public function getJsonConfig()
    {
        $config = parent::getJsonConfig();
            $config = $this->jsonDecoder->decode($config);
            //$config = json_decode($config, true);
            $config['qtyBoxes'] = $this->getQtyBoxes();
            $config = $this->jsonEncoder->encode($config);
        return $config;
    }

    /**
     * Get product images for configurable variations
     *
     * @return array
     * @since 100.1.10
     */
    protected function getQtyBoxes()
    {
        $qtyBoxes = [];
        foreach ($this->getAllowProducts() as $product) {
            $qtyBoxes[$product->getId()]['description'] = $product->getDescription();
            $product = $this->productRepository->get($product->getSku());
            $qtyBoxes[$product->getId()]['product_brand'] = $product->getAttributeText('product_brand');
            $qtyBoxes[$product->getId()]['size'] = $product->getAttributeText('size');
            $qtyBoxes[$product->getId()]['gtin'] = $product->getSku();
            $qtyBoxes[$product->getId()]['articlecode'] = $product->getResource()->getAttribute('articlecode')->getFrontend()->getValue($product);
            $qtyBoxes[$product->getId()]['color'] = ($product->getAttributeText('color')==null) ? null : $product->getAttributeText('color');
            $qtyBoxes[$product->getId()]['corner_height'] = ($product->getAttributeText('corner_height')==null) ? null : $product->getAttributeText('corner_height');
            $qtyBoxes[$product->getId()]['closure'] = ($product->getAttributeText('closure')==null) ? null : $product->getAttributeText('closure');
            $qtyBoxes[$product->getId()]['quality'] = ($product->getAttributeText('quality')==null) ? null : $product->getAttributeText('quality');
        }

        return $qtyBoxes;
    }
}
