<?php
    class Tejar_Salesdeal_Block_Product_Special extends Mage_Catalog_Block_Product_Abstract
    {
       function get_prod_count()
       {
          //unset any saved limits
          Mage::getSingleton('catalog/session')->unsLimitPage();
          return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 9;
       }// get_prod_count
       function get_cur_page()
       {
          return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
       }// get_cur_page
       /**
        * Retrieve loaded category collection
        *
        * @return Mage_Eav_Model_Entity_Collection_Abstract
       **/
       protected function _getProductCollection()
       {
            $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $tomorrow = mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
            $dateTomorrow = date('m/d/y', $tomorrow);
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
            $collection = $this->_addProductAttributesAndPrices($collection)
             ->addStoreFilter()
             ->addAttributeToSort('entity_id', 'desc') //<b>THIS WILL SHOW THE LATEST PRODUCTS FIRST</b>
             ->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
             ->addAttributeToFilter('special_to_date', array('or'=> array(0 => array('date' => true, 'from' => $dateTomorrow), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')
             ->setPageSize($this->get_prod_count())
             ->setCurPage($this->get_cur_page());
            $this->setProductCollection($collection);
            return $collection;
       }// _getProductCollection
    }// Mage_Catalog_Block_Product_New
    ?>