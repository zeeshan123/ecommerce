<?php 
class Tejar_CustomFilters_Block_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'tejar_customfilters/layer_filter_attribute';
    }
}