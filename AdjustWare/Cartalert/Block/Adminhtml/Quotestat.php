<?php
class AdjustWare_Cartalert_Block_Adminhtml_Quotestat extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();        
        $this->_controller = 'adminhtml_quotestat';
        $this->_blockGroup = 'adjcartalert';
        $this->_headerText = Mage::helper('adjcartalert')->__('Abandoned Carts Statistic');
        $this->_removeButton('add'); 
    }  
  
}