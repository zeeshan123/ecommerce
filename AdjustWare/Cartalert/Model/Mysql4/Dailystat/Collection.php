<?php

class AdjustWare_Cartalert_Model_Mysql4_Dailystat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/dailystat');
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        foreach ($this->_items as $item) {
            $item->setCurrency($currency);
        }
        return $this;
    }     
    
}