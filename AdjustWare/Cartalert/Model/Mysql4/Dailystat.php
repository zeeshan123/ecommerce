<?php
class AdjustWare_Cartalert_Model_Mysql4_Dailystat extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjcartalert/dailystat', 'id');
    }
}