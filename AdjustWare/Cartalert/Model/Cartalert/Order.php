<?php
class AdjustWare_Cartalert_Model_Cartalert_Order extends AdjustWare_Cartalert_Model_Cartalert
{
    const CARTALERT_INSTANCE_TYPE = 'order';

    public function getResource()
    {
        return Mage::getResourceSingleton('adjcartalert/cartalert_order');
    }
}