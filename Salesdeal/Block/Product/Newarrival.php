<?php
    class Mage_Catalog_Block_Product_Newarrival extends Mage_Catalog_Block_Product_List
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
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());


        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
            ->addAttributeToSort('news_from_date', 'desc')
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1)
        ;

        return $collection;
       }// _getProductCollection
    }// Mage_Catalog_Block_Product_New
    ?>