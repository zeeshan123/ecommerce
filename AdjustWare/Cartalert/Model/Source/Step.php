<?php
class AdjustWare_Cartalert_Model_Source_Step extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array(
            0 => array(
                'value' => '',
                'label' => '-'
            )
        );

        foreach (array('first','second','third') as $step)
            $options[] = array(
                'value'=> $step,
                'label' => Mage::helper('adjcartalert')->__(ucfirst($step). ' Email Template')
            );
        
        return $options;
    }
}