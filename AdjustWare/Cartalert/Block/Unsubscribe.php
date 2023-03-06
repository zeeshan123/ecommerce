<?php
class AdjustWare_Cartalert_Block_Unsubscribe extends Mage_Core_Block_Template
{
    public function isCustomerMode()
    {
        return Mage::getModel('adjcartalert/unsubscribe')->clientMode();
    }
    
    public function getHistory()
    {
        return Mage::registry('adjcartalert_history');
    }
    
    public function getConfirmed()
    {
        if(!is_object($this->getHistory())) {
            return false;
        }
        return (bool)$this->getHistory()->getConfirmed();
    }
    
}
