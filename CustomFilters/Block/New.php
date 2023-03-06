<?php
class Tejar_CustomFilters_Block_New extends Mage_Catalog_Block_Product_List
{
    protected function _construct()
    {
        parent::_construct();
        Mage::register('current_layer', $this->getLayer(), true);
    }
	
	 /**
     * Initialize blocks names
     */
    protected function _initBlocks()
    {
        parent::_initBlocks();

        $this->_attributeFilterBlockName = 'tejar_customfilters/layer_filter_attribute';
    }

   
    protected function _getProductCollection()
    {
		//echo "ZZZZZZZZZZZZZZZZZZZZZZ";die;
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getLayer()->getProductCollection();
        }
        return $this->_productCollection;
    }
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if($layer){
            return $layer;
        }
        return Mage::getSingleton('tejar_customfilters/layer');
    }
	
	public function addProductAttributesAndPrices($collection){
		return $this->_addProductAttributesAndPrices($collection);
	}
}