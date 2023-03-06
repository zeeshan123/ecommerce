<?php

class AdjustWare_Cartalert_Model_Mysql4_Stoplist_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/stoplist');
    }
}