<?php
class AdjustWare_Cartalert_Block_Adminhtml_Stoplist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    parent::__construct();
    $this->_controller = 'adminhtml_stoplist';
    $this->_blockGroup = 'adjcartalert';
    $this->_headerText = Mage::helper('adjcartalert')->__('Unsubscribed customers list');
    $this->_removeButton('add'); 
  }
}