<?php
namespace Twl\CategoryAttribute\Setup;
 
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
 
class InstallData implements InstallDataInterface
{
 
    private $eavSetupFactory;
 
    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'cms_block_list',
            [
                'type' => 'int',
                'label' => 'Cms Block',
                'input' => 'select',
                'sort_order' => 100,
                'source' => 'Magento\Catalog\Model\Category\Attribute\Source\Page',
                'global' => 2,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => null,
                'group' => 'General Information',
                'backend' => ''
            ]
        );
    }
}