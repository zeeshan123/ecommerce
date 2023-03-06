<?php
class AdjustWare_Cartalert_Model_Cartalert_Cart extends AdjustWare_Cartalert_Model_Cartalert
{
    const CARTALERT_INSTANCE_TYPE = 'cart';

    public function getResource()
    {
        return Mage::getResourceSingleton('adjcartalert/cartalert_cart');
    }
}