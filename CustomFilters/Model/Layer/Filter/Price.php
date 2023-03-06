<?php 
class Tejar_CustomFilters_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    protected function _createItem($label, $value, $count = 0)
    {
        return Mage::getModel('tejar_customfilters/layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }
	
	/**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPriceFloat()
    {
        if (!$this->hasData('max_price_float')) {
            $this->_collectPriceRange();
        }

        return $this->getData('max_price_float');
    }

    /**
     * Get minimum price from layer products set
     *
     * @return float
     */
    public function getMinPriceFloat()
    {
        if (!$this->hasData('min_price_float')) {
            $this->_collectPriceRange();
        }

        return $this->getData('min_price_float');
    }
	
	/**
     * Collect usefull information - max and min price
     *
     * @return Itactica_LayeredNavigation_Model_Catalog_Layer_Filter_Price
     */
    protected function _collectPriceRange()
    {
        $collection = $this->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $conditions = $select->getPart(Zend_Db_Select::WHERE);

        // Remove price sql conditions
        $conditionsNoPrice = array();
        foreach ($conditions as $key => $condition) {
            if (stripos($condition, 'price_index') !== false) {
                continue;
            }
            $conditionsNoPrice[] = $condition;
        }

        $this->setData('min_price_float', floor($collection->getMinPrice()));
        $this->setData('max_price_float', round($collection->getMaxPrice()));

        $select->setPart(Zend_Db_Select::WHERE, $conditionsNoPrice);

        // Restore all sql conditions
        $select->setPart(Zend_Db_Select::WHERE, $conditions);

        return $this;
    }

}