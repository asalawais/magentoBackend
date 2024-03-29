<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Promobanners\Controller\Adminhtml\Promobanners;

use Magento\Backend\App\Action;

class MassStatus extends \FIRSTSHOW\Promobanners\Controller\Adminhtml\Promobanners
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getPost('ids');
		if(!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
					$model = $this->_objectManager->create('FIRSTSHOW\Promobanners\Model\Promobanners')
						->load($id)
						->setStatus($this->getRequest()->getPost('status'))
						->save();
                }
				$this->messageManager->addSuccess(__('Total of %1 record(s) were successfully updated.', count($ids)));
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
