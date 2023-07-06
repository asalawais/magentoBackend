<?php
/**
 * Created by PhpStorm.
 * User: benvansteenbergen
 * Date: 06/08/2018
 * Time: 16:06
 */

namespace Itonomy\ProductVisibilityGrid\Block\Adminhtml\ProductVisibilityGrid;

use Magento\Store\Model\Store;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $productFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Type
     */
    private $type;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Itonomy\ProductVisibilityGrid\Model\ResourceModel\ProductVisibilityGrid\CollectionFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Eav\Model\Entity\Attribute\Source\Boolean $boolean,
        array $data = []
    ) {


        parent::__construct($context, $backendHelper, $data);


        $this->boolean = $boolean;
        $this->type = $type;
        $this->status = $status;
        $this->visibility = $visibility;

        $this->productFactory = $productFactory;
        $this->setId('productVisibilityGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    /**
     * Get store.
     *
     * @return Store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Returns the chosen store id.
     *
     * @return int Store id from the request if set, otherwise admin store id.
     */
    protected function _getStoreId()
    {
        return $this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
    }

    /**
     * Prepares the collection.
     *
     * @return Itonomy\ProductVisibilityGrid\Block\Adminhtml\ProductVisibilityGrid\Block
     */
    protected function _prepareCollection()
    {
        $collection = $this->productFactory->create()
            ->setStoreId($this->_getStoreId())
            ->prepareCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepares the grid columns.
     *
     * @return Itonomy\ProductVisibilityGrid\Block\Adminhtml\ProductVisibilityGrid\Block
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header'=> __('ID'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header'=> __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn(
            'type_id',
            [
                'header'=> __('Type'),
                'width' => '80px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => $this->type->getOptionArray(),
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header'=> __('SKU'),
                'width' => '120px',
                'index' => 'sku',
            ]
        );

        /*$this->addColumn(
            'in_flat_table',
            [
                'header'=> __('In Flat Table'),
                'width' => '80px',
                'index' => 'in_flat_table',
                'type'  => 'options',
                'options' => $this->boolean->getOptionArray()
            ]
        );*/

        if ($this->_getStoreId() != 0) {
            $this->addColumn(
                'in_website',
                [
                    'header'=> __('In Website'),
                    'width' => '80px',
                    'index' => 'in_website',
                    'type'  => 'options',
                    'options' => $this->boolean->getOptionArray()
                ]
            );

            /*$this->addColumn(
                'in_category',
                [
                    'header'=> __('In Category'),
                    'width' => '80px',
                    'index' => 'in_category',
                    'type'  => 'options',
                    'options' => $this->boolean->getOptionArray()
                ]
            );*/
            /*$this->addColumn(
                'qty',
                [
                    'header'=> __('Qty'),
                    'width' => '80px',
                    'type'  => 'number',
                    'index' => 'qty',
                ]
            );
            $this->addColumn(
                'in_stock',
                [
                    'header'=> __('In Stock'),
                    'width' => '80px',
                    'index' => 'in_stock',
                    'type'  => 'options',
                    'options' => $this->boolean->getOptionArray()
                ]
            );

            $this->addColumn(
                'in_price_index',
                [
                    'header'=> __('In Price Index'),
                    'width' => '80px',
                    'index' => 'in_price_index',
                    'type' => 'options',
                    'options' => $this->boolean->getOptionArray()
                ]
            );

            $this->addColumn(
                'visibility',
                [
                    'header'=> __('Visibility'),
                    'width' => '80px',
                    'index' => 'visibility',
                    'type'  => 'options',
                    'options' => $this->visibility->getOptionArray()
                ]
            );*/
        }

        $this->addColumn(
            'qty',
            [
                'header'=> __('Qty'),
                'width' => '80px',
                'type'  => 'number',
                'index' => 'qty',
            ]
        );
        $this->addColumn(
            'in_stock',
            [
                'header'=> __('In Stock'),
                'width' => '80px',
                'index' => 'in_stock',
                'type'  => 'options',
                'options' => $this->boolean->getOptionArray()
            ]
        );

        $this->addColumn(
            'in_price_index',
            [
                'header'=> __('In Price Index'),
                'width' => '80px',
                'index' => 'in_price_index',
                'type' => 'options',
                'options' => $this->boolean->getOptionArray()
            ]
        );
        $store = $this->_getStoreId();
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );


        $this->addColumn(
            'visibility',
            [
                'header'=> __('Visibility'),
                'width' => '80px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => $this->visibility->getOptionArray()
            ]
        );

        $this->addColumn(
            'is_online_in_cat',
            [
                'header'=> __('Is visible in category'),
                'width' => '80px',
                'index' => 'is_online_in_cat',
                'type'  => 'options',
                'options' => $this->boolean->getOptionArray()
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'=> __('Status'),
                'width' => '80px',
                'index' => 'status',
                'type'  => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Send Offer'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => __('S'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/S'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ],
                    [
                        'caption' => __('M'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/M'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ],
                    [
                        'caption' => __('XL'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/XL'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ],
                    [
                        'caption' => __('SPEC_1'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/SPEC_1'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ],
                    [
                        'caption' => __('SPEC_2'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/SPEC_2'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ],
                    [
                        'caption' => __('SPEC_3'),
                        'url'     => [
                            'base'=> ('*/*/reindex'.'/shipping/SPEC_3'.'/store/'.$this->getRequest()->getParam('store'))
                        ],
                        'field'   => 'id'
                    ]

                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepares the grid mass action.
     *
     * @return Itonomy\ProductVisibilityGrid\Block\Adminhtml\\Block
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('sendoffers', [
            'label'=> __('Send Offer -> S'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'S','store'=>$this->getRequest()->getParam('store')]),
        ]);
        $this->getMassactionBlock()->addItem('sendofferm', [
            'label'=> __('Send Offer -> M'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'M','store'=>$this->getRequest()->getParam('store')]),
        ]);
        $this->getMassactionBlock()->addItem('sendofferxl', [
            'label'=> __('Send Offer -> XL'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'XL','store'=>$this->getRequest()->getParam('store')]),
        ]);
        $this->getMassactionBlock()->addItem('sendofferspec1', [
            'label'=> __('Send Offer -> SPEC_1'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'SPEC_1','store'=>$this->getRequest()->getParam('store')]),
        ]);
        $this->getMassactionBlock()->addItem('sendofferspec2', [
            'label'=> __('Send Offer -> SPEC_2'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'SPEC_2','store'=>$this->getRequest()->getParam('store')]),
        ]);
        $this->getMassactionBlock()->addItem('sendofferspec3', [
            'label'=> __('Send Offer -> SPEC_3'),
            'url'  => $this->getUrl('*/*/massReindex', ['shipping'=>'SPEC_3','store'=>$this->getRequest()->getParam('store')]),
        ]);

        return $this;
    }

    /**
     * Returns the grid (ajax update) URL.
     *
     * @return string URL to request an update.
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/grid',
            ['_current'=>true]
        );
    }

    /**
     * Returns a row URL.
     *
     * @param Varien_Object Row data.
     * @return string URL to product edit page.
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', [
                'store'=>$this->getRequest()->getParam('store'),
                'id'=>$row->getId()]);
    }

    /**
     * Applies the selected column filter to the collection.
     *
     * @param Magento\Framework\Block\Widget\Grid\Column Column data.
     * @return Itonomy\ProductVisibilityGrid\Block\Adminhtml\ProductVisibilityGrid\Block
     */
    protected function _addColumnFilterToCollection($column)
    {
        $value = $column->getFilter()->getValue();
        if (!isset($value)) {
            parent::_addColumnFilterToCollection($column);
            return $this;
        }

        switch ($column->getId()) {
            case 'in_flat_table':
                $this->getCollection()->addInFlatTableFilter((int)$value);
                break;
            case 'in_website':
                $this->getCollection()->addInWebsiteFilter((int)$value);
                break;
            case 'in_category':
                $this->getCollection()->addInCategoryFilter((int)$value);
                break;
            case 'in_stock':
                $this->getCollection()->addInStockFilter((int)$value);
                break;
            case 'in_price_index':
                $this->getCollection()->addInPriceIndexFilter((int)$value);
                break;
            case 'is_online_in_cat':
                $this->getCollection()->addIsVisibleInCategoryFilter((int)$value);
                break;
            default:
                parent::_addColumnFilterToCollection($column);
                break;
        }

        return $this;
    }
}
