<?php

class AdjustWare_Cartalert_Adminhtml_Cartalert_QuotestatController extends AdjustWare_Cartalert_Controller_Adminhtml_Abstract
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('newsletter/adjcartalert/quotestat');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('adjcartalert/quotestat')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Abandoned Carts Statistic'), Mage::helper('adminhtml')->__('Abandoned Cart Statistic'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_quotestat'));
        $this->renderLayout();
    }

    public function viewAction()
    {
        $quotestatId     = $this->getRequest()->getParam('id');
        $quotestatModel  = Mage::getModel('adjcartalert/quotestat')->load($quotestatId);
 
        if ($quotestatModel->getId()) {
 
            Mage::register('quotestat_data', $quotestatModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('adjcartalert/quotestat');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Abandoned Carts Statistic'), Mage::helper('adminhtml')->__('Abandoned Carts Statistic'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Abandoned Carts Statistic'), Mage::helper('adminhtml')->__('Abandoned Carts Statistic'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
                          
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjcartalert')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }    
    }
        
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('adjcartalert/adminhtml_quotestat_grid')->toHtml()
        );
    }
	
}