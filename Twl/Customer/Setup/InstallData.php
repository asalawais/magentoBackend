<?php  
  namespace Twl\Customer\Setup;

  use Magento\Customer\Setup\CustomerSetupFactory;
  use Magento\Customer\Model\Customer;
  use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
  use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
  use Magento\Framework\Setup\InstallDataInterface;
  use Magento\Framework\Setup\ModuleContextInterface;
  use Magento\Framework\Setup\ModuleDataSetupInterface;

  /**
   * Install data
   * @codeCoverageIgnore
   */
  class InstallData implements InstallDataInterface
  {

      /**
       * CustomerSetupFactory
       * @var CustomerSetupFactory
       */
      protected $customerSetupFactory;

      /**
       * $attributeSetFactory
       * @var AttributeSetFactory
       */
      private $attributeSetFactory;

      /**
       * initiate object
       * @param CustomerSetupFactory $customerSetupFactory
       * @param AttributeSetFactory $attributeSetFactory
       */
      public function __construct(
          CustomerSetupFactory $customerSetupFactory,
          AttributeSetFactory $attributeSetFactory
      )
      {
          $this->customerSetupFactory = $customerSetupFactory;
          $this->attributeSetFactory = $attributeSetFactory;
      }

      /**
       * install data method
       * @param ModuleDataSetupInterface $setup
       * @param ModuleContextInterface $context
       */
      public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
      {

          /** @var CustomerSetup $customerSetup */
          $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

          $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
          $attributeSetId = $customerEntity->getDefaultAttributeSetId();

          /** @var $attributeSet AttributeSet */
          $attributeSet = $this->attributeSetFactory->create();
          $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
          /**
           * customer registration form default field mobile number
           */
          $customerSetup->addAttribute(Customer::ENTITY, 'contact_number', [
              'type' => 'varchar',
              'label' => 'Contact Number',
              'input' => 'text',
              'required' => true,
              'visible' => true,
              'user_defined' => true,
              'sort_order' => 1000,
              'position' => 1000,
              'system' => 0,
          ]);
          //add attribute to attribute set

          /*
          //You can use this attribute in the following forms
          adminhtml_checkout
          adminhtml_customer
          adminhtml_customer_address
          customer_account_create
          customer_account_edit
          customer_address_edit
          customer_register_address
          */

          $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'contact_number')
              ->addData([
                  'attribute_set_id' => $attributeSetId,
                  'attribute_group_id' => $attributeGroupId,
                  'used_in_forms' => ['adminhtml_customer', 'customer_account_create','customer_account_edit'],
              ]);

          $attribute->save();


          $attribute->save();


      }
  }