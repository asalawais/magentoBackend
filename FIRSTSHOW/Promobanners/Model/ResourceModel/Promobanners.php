<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Promobanners\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Promobanners extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\DateTime
     */
	protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
		$this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }
	
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('promobanners', 'promobanners_id');
    }
	
	protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }
	
	
	protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
			try {
				$uploader = $this->_fileUploaderFactory->create(['fileId' => 'filename']);
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				
			} catch (\Exception $e) {
				return $this;
			}
			$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('promobanners/');
			$uploader->save($path);
			$fileName = $uploader->getUploadedFileName();
			if ($fileName) {
				$object->setData('filename', $fileName);
				$object->save();
			}
			return $this;
		}
    }
}
