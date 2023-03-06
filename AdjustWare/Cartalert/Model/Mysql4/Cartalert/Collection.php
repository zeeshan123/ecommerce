<?php
/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class AdjustWare_Cartalert_Model_Mysql4_Cartalert_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/cartalert');
    }
    
    public function addReadyForSendingFilter()
    {
        $this->getSelect()->where('sheduled_at < ?',now());
            //->where('status = ?', 'pending');
        return $this;
    } 
}