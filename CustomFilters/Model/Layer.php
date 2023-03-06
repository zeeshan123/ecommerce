<?php
class Tejar_CustomFilters_Model_Layer extends Mage_Catalog_Model_Layer
{
	
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'STORE_'.Mage::app()->getStore()->getId()
                . '_NEW_PRODUCTS_'
                . '_CUSTGROUP_' . Mage::getSingleton('customer/session')->getCustomerGroupId();
                //. '_CUSTGROUP_' . parent::getStateKey();
        }

        return $this->_stateKey;
    }

    public function getStateTags(array $additionalTags = array())
    {
        $additionalTags = array_merge($additionalTags, array('new_products'));
        return $additionalTags;
    }

	 /**
     * Add views count
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Product_Collection
     */
    public function addViewsCount($from = '', $to = '', $collection)
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = (int)$eventType->getId();
                break;
            }
        }

        $collection->getSelect()->reset()
            ->from(
               array('report_table_views' => $collection->getTable('reports/event')),
                array('views' => 'COUNT(report_table_views.event_id)'))
         
            ->where('report_table_views.event_type_id = ?', $productViewEvent)
            ->group('e.entity_id')
            ->order('views desc')
            ->having('COUNT(report_table_views.event_id) > ?', 0);

        if ($from != '' && $to != '') {
            $collection->getSelect()
                ->where('logged_at >= ?', $from)
                ->where('logged_at <= ?', $to);
        }

        //$collection->_useAnalyticFunction = true;
        return $collection;
    }


	
    public function getProductCollection()
    {
		
		//echo $this->getCurrentCategory()->getId(); die;
        /*if (isset($this->_productCollections['new_products'])) {
            $collection = $this->_productCollections['new_products'];
        } else {
            $collection = $this->_getCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections['new_products'] = $collection;
        }
		//echo $collection->getSelect();die;
		$this->setProductCollection($collection);
        return $collection;*/
		
		if(isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
			
            //$collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
            $collection =  $this->_getCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }
	
   /**
     * Prepare product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    { 
		  $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            //->addSearchFilter()
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();
		//--- If 'Display Out of stock Products' is set to NO apply this stock filter..	
		if(!Mage::getStoreConfig('cataloginventory/options/show_out_of_stock')){	
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection( $collection );
        }
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
    // echo "---> ".$collection->getSize();die;
        return $this;
    }

	
    protected function _getCollection()
    {
		$currentModuleName = Mage::app()->getFrontController()->getRequest()->getModuleName();
		
		if($currentModuleName == 'new-arrival' || $currentModuleName == 'newproducts'){
			$todayStartOfDayDate  = Mage::app()->getLocale()->date()
				->setTime('00:00:00')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
				
				$todayEndOfDayDate  = Mage::app()->getLocale()->date()
				->setTime('23:59:59')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
				/** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
				$collection = Mage::getResourceModel('catalog/product_collection')
					->setVisibility(Mage::getSingleton('catalog/product_visibility')
					->getVisibleInCatalogIds());    
					$collection = $collection->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents()
					->addAttributeToSelect(Mage::getSingleton('catalog/config')
					->getProductAttributes())
					->addUrlRewrite()
					->addStoreFilter()
					->addAttributeToFilter('news_from_date', array('and'=> array(
					0 => array('date' => true, 'to' => $todayEndOfDayDate),
					1 => array('is' => new Zend_Db_Expr('not null')))
					), 'left')
					->addAttributeToFilter('news_to_date', array('or'=> array(
					0 => array('date' => true, 'from' => $todayStartOfDayDate),
					1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
				->addAttributeToSort('updated_at', 'desc');
				
				//echo $collection->getSelect();die;
				
		}elseif($currentModuleName == 'best-seller'){
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
						->addStoreFilter($storeId);
			
			//--- Set Order based on the Views Count, same order in maxQty Array..
				$sortOrderRequest =  Mage::app()->getRequest()->getParam('order');
				if(isset($sortOrderRequest)==null){
					$collection->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $maxQty).')'));
				}
		}elseif($currentModuleName == 'deals'){
				$storeId = Mage::app()->getStore()->getId();
				$todayStartOfDayDate  = Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
				$todayEndOfDayDate  = Mage::app()->getLocale()->date()->setTime('23:59:59')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
				//$todayDate  = Mage::getModel('core/date')->gmtDate('Y-m-d');
				
				//echo $todayDate;die;
				$tomorrow = mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
				$dateTomorrow = date('m/d/y', $tomorrow);
				
				$collection = Mage::getResourceModel('catalog/product_collection')
							->addMinimalPrice()
							->addFinalPrice()
							->addTaxPercents()
							->addAttributeToSelect(Mage::getSingleton('catalog/config')
							->getProductAttributes())
							->addUrlRewrite()
							->addStoreFilter()
							->addAttributeToFilter(
								array(
									array('attribute' => 'special_price', 'is'=>new Zend_Db_Expr('not null'))
								)
							)
							->addAttributeToFilter('special_from_date', array('and'=> array(
							0 => array('date' => true, 'to' => $todayEndOfDayDate),
							1 => array('is' => new Zend_Db_Expr('not null')))
							), 'left')
							->addAttributeToFilter('special_to_date', array('or'=> array(
							0 => array('date' => true, 'from' => $todayStartOfDayDate),
							1 => array('is' => new Zend_Db_Expr('null')))
							), 'left')
							
						->addStoreFilter($storeId);
			
				//--- Set Order based on the Views Count, same order in maxQty Array..
				$sortOrderRequest =  Mage::app()->getRequest()->getParam('order');
				if(isset($sortOrderRequest)==null){
					 $collection->setOrder('updated_at', 'DESC');
				}
				//echo $collection->getSelect();die;
		}elseif($currentModuleName == 'most-viewed'){
			
			//--- Store ID
			$storeId = Mage::app()->getStore()->getId(); 
			//--- Get most viewed products for last 30 days..
			$today = time();
			$last  = $today - (60*60*24*07);
			$from  = date("Y-m-d", $last);
			$to    = date("Y-m-d", $today);
			
			$storeId = Mage::app()->getStore()->getId();
			$attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$table_prefixx = Mage::getConfig()->getTablePrefix(); 
			
			$queryText = "SELECT COUNT(report_table_views.event_id) AS `views`, `e`.* FROM `report_event` AS `report_table_views` INNER JOIN `catalog_product_entity` AS `e` ON e.entity_id = report_table_views.object_id AND e.entity_type_id = 4 WHERE (report_table_views.event_type_id = 1) GROUP BY `e`.`entity_id` HAVING (COUNT(report_table_views.event_id) > 10) ORDER BY `views` DESC, `e`.`entity_id` desc";
			
			$res = $write->query($queryText);
			while($row = $res->fetch()) {
				$maxQty[]=$row['entity_id'];
			}
		
				$collection = Mage::getModel('catalog/product')
							->getCollection()
                        ->addAttributeToSelect($attributes)
                        ->addAttributeToFilter('entity_id', array('in' => $maxQty))
						->addStoreFilter($storeId);
		
		//--- Set Order based on the Views Count, same order in maxQty Array..
				$sortOrderRequest =  Mage::app()->getRequest()->getParam('order');
				if(isset($sortOrderRequest)==null){
					$collection->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $maxQty).')'));
				}
		
		}elseif($currentModuleName == 'featured'){
			
			$storeId    = Mage::app()->getStore()->getId();
			$collection = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToFilter(array(array('attribute' => 'featured', 'eq' => '1')))
				->addAttributeToSelect('*')
				->setStoreId($storeId)
				->addStoreFilter($storeId)
				->setPageSize($this->get_prod_count())
				->setCurPage($this->get_cur_page());
			//--- Set Order based on the Views Count, same order in maxQty Array..
				$sortOrderRequest =  Mage::app()->getRequest()->getParam('order');
				if(isset($sortOrderRequest)==null){
					 $collection->setOrder('updated_at', 'DESC');
				}
		}
	
        return $collection;
    }
	
	function get_prod_count()
       {
          //unset any saved limits
          Mage::getSingleton('catalog/session')->unsLimitPage();
          return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 12;
       }// get_prod_count
	   
	function get_cur_page()
    {
	  return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
    }// get_cur_page
	
	 function get_order()
	{
		return (isset($_REQUEST['order'])) ? ($_REQUEST['order']) : 'position';
	}// get_order

    function get_order_dir()
	{
		return (isset($_REQUEST['dir'])) ? ($_REQUEST['dir']) : 'desc';
	}// get_direction
}

