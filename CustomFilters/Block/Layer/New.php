<?php
class Tejar_CustomFilters_Block_Layer_New extends Mage_Catalog_Block_Layer_View
{
    public function getLayer()
    {
        if (!$this->hasData('_layer')){
            $layer = Mage::getSingleton('tejar_customfilters/layer');
            $this->setData('_layer', $layer);
        }
        return $this->getData('_layer');
    }
	
    protected function _initBlocks()
    {
        parent::_initBlocks();
        //$this->_categoryFilterBlockName     = 'tejar_customfilters/layer_filter_category';
        $this->_attributeFilterBlockName    = 'tejar_customfilters/layer_filter_attribute';
        $this->_priceFilterBlockName        = 'tejar_customfilters/layer_filter_price';
        $this->_decimalFilterBlockName      = 'tejar_customfilters/layer_filter_decimal';
    }
	
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
            ->setLayer($this->getLayer());
		

        $this->setChild('layer_state', $stateBlock);
		//echo "<pre>"; var_dump($this->_categoryBlockName);
       //$this->setChild('category_filter', $categoryBlock);

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
			  if ($attribute->getAttributeCode() == 'price') {
                $filterBlockName = $this->_priceFilterBlockName;
            } elseif ($attribute->getBackendType() == 'decimal') {
                $filterBlockName = $this->_decimalFilterBlockName;
            } else {
                $filterBlockName = $this->_attributeFilterBlockName;
            }
            $this->setChild($attribute->getAttributeCode() . '_filter',
            $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init());
        }

        $this->getLayer()->apply();
        return $this;
    }
}