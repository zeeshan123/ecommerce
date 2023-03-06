<?php
Class AdjustWare_Cartalert_Controller_Adminhtml_Abstract extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
             parent::preDispatch();
             if(Mage::helper('core')->isModuleEnabled('Aitoc_Common')) {
                 Mage::getSingleton('aitoc_common/cron')->validateCronDate()->showError();
             }
    }
}