<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FIRSTSHOW\Promobanners\Controller\Adminhtml\Promobanners;

class Save extends \FIRSTSHOW\Promobanners\Controller\Adminhtml\Promobanners
{
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        if ($data) {
			if(isset($data['identifier']) && !isset($data['promobanners_id'])){
				$existBanners = $this->_objectManager->create('FIRSTSHOW\Promobanners\Model\Promobanners')
					->getCollection()
					->addFieldToFilter('identifier', $data['identifier']);
				if(count($existBanners)>0){
					$this->messageManager->addError(__('Identifier already exist. Please use other identifier'));
					return $resultRedirect->setPath('*/*/');
				}
			}
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('FIRSTSHOW\Promobanners\Model\Promobanners')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // init model and set data

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the item.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
