<?php

class AdjustWare_Cartalert_Adminhtml_Cartalert_DailystatController extends AdjustWare_Cartalert_Controller_Adminhtml_Abstract
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('newsletter/adjcartalert/dailystat');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('adjcartalert/quotestat')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Daily Statistic'), Mage::helper('adminhtml')->__('Daily Statistic'));
        return $this;
    }   
   
    public function indexAction() {
        $periodType = $this->getRequest()->getParam('period_type');
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        
        if($periodType && (!$from || !$to))
        {
            
            Mage::getSingleton('core/session')->addError($this->__('Please select correct from/to values'));
            $this->getRequest()->setParam('period_type','');
        }        
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_dailystat_statistics'));
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_dailystat_cronmanage'));
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_dailystat_filter'));
        $this->_addContent($this->getLayout()->createBlock('adjcartalert/adminhtml_dailystat'));
        $this->renderLayout();
    }
   
	
    public function cronmanageAction()
    {
        
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        $this->getResponse()->setBody(
            Mage::getModel('adjcartalert/cronstat')->createTask('AdjustWare_Cartalert_Model_Dailystat', 'collectDay', $from, $to)
        );
    }        
    
}