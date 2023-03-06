<?php

class AdjustWare_Cartalert_Adminhtml_Cartalert_StoplistController extends AdjustWare_Cartalert_Controller_Adminhtml_Abstract
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('newsletter/adjcartalert/stoplist');
    }
    
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('newsletter/adjcartalert/stoplist');
        $this->_addBreadcrumb($this->__('Carts Alerts'), $this->__('Stoplist'));
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_stoplist'));
        $this->renderLayout();
    }

    public function editAction() {
        $this->_redirect('*/*/');
    }
 
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('cartalert');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjcartalert')->__('Please select emails(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('adjcartalert/stoplist')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction() {
		if ($this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('adjcartalert/stoplist');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adjcartalert')->__('Customer email was deleted from stoplist'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	
}