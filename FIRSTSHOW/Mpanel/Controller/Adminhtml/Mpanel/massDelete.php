<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel;

use Magento\Backend\App\Action;

class massDelete extends \FIRSTSHOW\Mpanel\Controller\Adminhtml\Mpanel
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
					$model = $this->_objectManager->create('FIRSTSHOW\Mpanel\Model\Mpanel')
						->load($id)
						->delete();
                }
				$this->messageManager->addSuccess(__('Total of %1 record(s) were successfully deleted.', count($ids)));
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
