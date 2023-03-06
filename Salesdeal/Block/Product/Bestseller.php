<?php
    class Mage_Catalog_Block_Product_Bestseller extends Mage_Catalog_Block_Product_List
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
           $storeId = Mage::app()->getStore()->getId();
    	$attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table_prefixx = Mage::getConfig()->getTablePrefix(); 
        $res = $write->query("select max(qo) as des_qty,`product_id`,`parent_item_id` FROM (select sum(`qty_ordered`) AS qo,`product_id`,created_at,store_id,`parent_item_id` from ".$table_prefixx."sales_flat_order_item Group By `product_id`) AS t1 where  parent_item_id is null Group By `product_id` order By des_qty desc"); 
        
        while ($row = $res->fetch()) $maxQty[]=$row['product_id'];
        $collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect($attributes)
                        ->addAttributeToFilter('entity_id', array('in' => $maxQty))
						->addStoreFilter($storeId);;

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        
        $collection->setPageSize($this->getConfig('qty'))->setCurPage(1);
        Mage::getModel('review/review')->appendSummary($collection);     
        //var_dump($collection->getAllIds());                   
        return $collection;
       }// _getProductCollection
    }// Mage_Catalog_Block_Product_New
    ?>