<?php

//require_once 'Itactica/LayeredNavigation/Model/Catalog/Layer/Filter/Item.php'; 
class Tejar_CustomFilters_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
	public function isCatSelected($categoryLabel)
    {
        $appliedFilters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
       
		foreach ($appliedFilters as $item) {
			
            if ($item->getFilter()->getRequestVar() == 'cat' && $item->getLabel() == $categoryLabel) {
                return true;
            }
        }
        return false;
    }
	
	protected $_helper;

    protected function _helper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper('tejar_customfilters');
        }
        return $this->_helper;
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getU555rl()
    {
		
        if (!$this->_helper()->isEnabled() 
            || Mage::getSingleton('core/design_package')->getPackageName() != 'intenso') {
            return parent::getUrl();
        }
	//echo "ZEEEEEEEEEEEEEEEEEE";die;
        $values = $this->getFilter()->getValues();
	
        if (!empty($values)) {
            $tmp = array_merge($values, array($this->getValue()));
            // Sort filters - small SEO improvement
            asort($tmp);
            $values = implode(Stackexchange_Newproducts_Helper_Data::MULTIPLE_FILTERS_DELIMITER, $tmp);
        } else {
            $values = $this->getValue();
        }


        return $this->_helper()->getFilterUrl(array(
            $this->getFilter()->getRequestVar() => $values
        ));
    }
	
	/**
     * Check if current filter is selected
     *
     * @return boolean 
     */
    public function isSelected()
    {
        $values = $this->getFilter()->getValues();
        if (in_array($this->getValue(), $values)) {
            return true;
        }
        return false;
    }

}