<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_LayeredNavigation
 * @copyright   Copyright (c) 2014 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 * @author      Zeeshan <zeeshan.zeeshan123@gmail.com>
 */
/*********======================= ZEE CODE =========================********/
class Tejar_CatalogSearch_Model_Layer extends Itactica_LayeredNavigation_Model_CatalogSearch_Layer
{
	
	
	public function getProductCollection()
    {
		
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
			
            $collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
			if(!$collection->getsize()){
				$myURLSearchquery    = Mage::helper('catalogsearch')->getQueryText();
				//--- Serch by Models collection
				//---Check if model attribute exists, search only by SKU if not
				$entity = 'catalog_product';
				$code = 'model';
				$attr = Mage::getResourceModel('catalog/eav_attribute')
					->loadByCode($entity,$code);
				if ($attr->getId()) {
					$collection = Mage::getResourceModel('catalog/product_collection');
					$collection->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
					$collection->addAttributeToFilter(
					array(
						array('attribute'=>'model','=' => $myURLSearchquery)
						)
					);
					
					$collection->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
					->setPageSize(6)
					->addAttributeToFilter('status',array('eq' => '1'))
					->addAttributeToFilter('visibility', array('neq' => 1)) //--- Not visible individually
					->addAttributeToFilter('visibility', array('neq' => 2)) //--- Only catalog visibility
					->addStoreFilter($this->getStoreId());
				}
				
				//--- Search by SKU collection
				if(!$collection->getSize()){
					$collection = Mage::getResourceModel('catalog/product_collection');
					$collection->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price','sku', 'thumbnail', 'short_description', 'url_key'));
					$collection->addAttributeToFilter(
					array(
						array('attribute'=>'sku','=' => $myURLSearchquery)
						)
					);
					
					$collection->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
					->setPageSize(6)
					->addAttributeToFilter('status',array('eq' => '1'))
					->addAttributeToFilter('visibility', array('neq' => 1)) //--- Not visible individually
					->addAttributeToFilter('visibility', array('neq' => 2)) //--- Only catalog visibility
					->addStoreFilter($this->getStoreId());
				}
			
				//--- DISPLAY PARENT: IF Associate is not visibile individually		
				if(!$collection->getSize()){
					$collection = Mage::getResourceModel('catalog/product_collection');
					$collection->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
					
					//---Check if model attribute exists, search only by SKU if not
					$entity = 'catalog_product';
					$code = 'model';
					$attr = Mage::getResourceModel('catalog/eav_attribute')
						->loadByCode($entity,$code);
					if ($attr->getId()) {
						$collection->addAttributeToFilter(
						array(
							array('attribute'=>'model','=' => $myURLSearchquery),
							array('attribute'=>'sku','=' => $myURLSearchquery)
							)
						);
					}else{
						$collection->addAttributeToFilter(
						array(
							//array('attribute'=>'model','=' => $myURLSearchquery),
							array('attribute'=>'sku','=' => $myURLSearchquery)
							)
						);
					}
					
					//->addAttributeToFilter('sku', array('like' => $myURLSearchquery."%"))
					$collection->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
					->setPageSize(1)
					->addStoreFilter($this->getStoreId());
					//--- Get parent product by simple....
					foreach($collection as $item){
						$parentProduct =   Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId());
					}
					
					if(isset($parentProduct) && isset($parentProduct[0])){
						$collection = Mage::getResourceModel('catalog/product_collection');
						$collection->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
						$collection->addExpressionAttributeToSelect('query_text', '{{name}}', 'name');
						$collection->addFieldToFilter('entity_id',array('eq'=>$parentProduct[0]))
						//->addAttributeToSort('model', 'ASC')
						->setPageSize(6);
					}
				}
			
				$this->prepareProductCollection($collection, true);
				$this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
			 }else{
				
				$collection =  Mage::getResourceModel('catalogsearch/fulltext_collection');
				$collection = $this->getAjaxProductCollection($collection);
			
				$this->prepareProductCollection($collection, false);
				$this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
			}
        }
        return $collection;
    }
	
   
	
	/**
     * Initialize product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
	public function prepareProductCollection($collection, $isSku)
    {
         $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();
			
			//$collection->getSelect()->limit(20);	
		//--- Add search filter only if product was NOT fetched by SKU	
		if($isSku===false){
			$collection ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText());
		}
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        return $this;
    }
	
	protected function getAjaxProductCollection($collection)
    {
		//--- Initialize All Values....
			$myURLSearchquery    = addslashes(Mage::helper('catalogsearch')->getQueryText());
			$myURLSearchOrder    = Mage::app()->getRequest()->getParam('order');
			$myURLSearchOrderDir = Mage::app()->getRequest()->getParam('dir');
			//echo $myURLSearchquery;die;
			$myQueryCases1       = "";
			$myQueryCases2       = "";
			$myQueryCases3       = "";
			$myQueryCases4       = "";
			$myQueryCases5 		 = "";
			$myQueryCases6 		 = "";
			$myQueryCases7 		 = "";
			$myQueryCases8 		 = "";
			$orderBy             = "";
			$searchParamsArray   = array();
			
		//--- Reset ORDER BY AND WHERE Clases from Current Colleciton..
			//$collection->getSelect()->reset(Zend_Db_Select::ORDER);
			//$collection->getSelect()->reset(Zend_Db_Select::WHERE);
			
		//-- Get product name table, (if flat catalog is disabled)
			if (!Mage::helper('catalog/product_flat')->isEnabled()) {
				//$table = 'catalog_product_flat_'.Mage::app()->getStore()->getStoreId();
				//catalog_product_entity_varchar
				$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'name');
				$attrId = $attribute->getId();

				$table = 'catalog_product_entity_varchar';
				$collection->getSelect()->join($table, 'e.entity_id ='.$table.'.entity_id AND '.$table.'.attribute_id = '.$attrId, $table.'.value');
				$collection->distinct(true);
				//if (!Mage::app()->getRequest()->isXmlHttpRequest()) {
					//$collection->getSelect()->columns($table.'.entity_id')->group($table.'.entity_id');
				//}
				//
				$nameAttribute = $table.'.value';
            }else{
				$nameAttribute = 'name';
			}
			
		//--- SET product visibility...
		//Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		if(strpos($myURLSearchquery, ' ')){
				$myURLSearchqueryArray = explode(' ', $myURLSearchquery);
				$wordIndex = 0;
				$myVar = "";
				
			//--- Generate CASES for ORDER BY clause for multiple words 
				$myQueryCases1 .= $myURLSearchquery;
				$myQueryCases2 .= "% ".$myURLSearchquery;
				$myQueryCases3 .= $myURLSearchquery." %";
				$myQueryCases4 .= "% ".$myURLSearchquery." %";
				$myQueryCases8 .= "%".$myURLSearchquery."%";
				$myQueryRegex  = "";
				
				
				foreach($myURLSearchqueryArray as $myQuery){
				//if(strlen($myQuery)>1){	
					if($myQuery[strlen($myQuery)-1]!='s'){
							$considerPlural = "s";
						}else{
							$considerPlural = "";
						}
						
					array_push($searchParamsArray, array('like' =>  $myQuery."%"));
					array_push($searchParamsArray, array('like' =>  "%"." ".$myQuery." "."%"));
					array_push($searchParamsArray, array('like' =>  "%_ ".$myQuery."%"));
					array_push($searchParamsArray, array('like' =>  "%_".$myQuery));
					array_push($searchParamsArray, array('like' =>  "%_".$myQuery." %"));
					array_push($searchParamsArray, array('like' =>  "%_".$myQuery.$considerPlural." %"));
					
					array_push($searchParamsArray, array('like' =>  substr($myQuery, 0, strlen($myQuery)-1)." _%"));
					
				//--- CONTINUE: Generate CASES for ORDER BY clause for search with multiple words..
				
					$wordIndex++;
					$myQueryRegex  .= "%".$myQuery."%";
				}
				
				array_push($searchParamsArray, array('like' =>  $myURLSearchquery[0]. $myURLSearchquery[1]."_%"));
				
			}else{
				//--- Consider Plural as well............
				if($myURLSearchquery[strlen($myURLSearchquery)-1]!='s'){
					$considerPlural = "s";
				}elseif($myURLSearchquery[strlen($myURLSearchquery)-1]=='s'){
					$considerPlural = "";
					array_push($searchParamsArray, array('like' =>  substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %"));
					array_push($searchParamsArray, array('like' =>  "% ".substr($myURLSearchquery, 0,strlen($myURLSearchquery)-1)." %"));
					array_push($searchParamsArray, array('like' =>  "% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)));
					array_push($searchParamsArray, array('like' =>  "%".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)."%"));
				}
				
				
				array_push($searchParamsArray, array('like' =>  $myURLSearchquery." %"));
				array_push($searchParamsArray, array('like' =>  "% ".$myURLSearchquery." %")); 
				array_push($searchParamsArray, array('like' =>  "% ".$myURLSearchquery));
				array_push($searchParamsArray, array('like' =>  $myURLSearchquery."_%"));
				array_push($searchParamsArray, array('like' =>  "%".$myURLSearchquery."%"));
				//array_push($searchParamsArray, array('like' =>  "% ".$myURLSearchquery.$considerPlural." %"));
				//array_push($searchParamsArray,array('like' =>  "%".$myURLSearchquery.$considerPlural."%"));
				//array_push($searchParamsArray,array('like' =>  "%".$myURLSearchquery.$considerPlural));
				//array_push($searchParamsArray, array('eq' => $myURLSearchquery));
			}
			
			if(($key = array_search("%".$myURLSearchquery."%", $searchParamsArray)) !== false) {
					unset($searchParamsArray[$key]);
				}
				
				//--- Again check if search query contains single or multiple words!
				if(!strpos($myURLSearchquery, ' ')){
					
					
					$orderBy  .= "CASE WHEN ".$nameAttribute." LIKE '".$myURLSearchquery." %'  THEN 1 WHEN ".$nameAttribute." LIKE '% ".$myURLSearchquery." %'  THEN 2 WHEN ".$nameAttribute." LIKE '% ".$myURLSearchquery."' THEN 3 WHEN ".$nameAttribute." LIKE '".$myURLSearchquery."_%' THEN 4 WHEN ".$nameAttribute." LIKE '%".$myURLSearchquery."%' THEN 5 WHEN ".$nameAttribute." LIKE '".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %' THEN 6 WHEN ".$nameAttribute." LIKE '% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %' THEN 7 WHEN ".$nameAttribute." LIKE '% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)."' THEN 8 ELSE 9 END, ".$nameAttribute;
				}else{
					
					//---if search query contains multiple words, prepare Cases For ORDER BY Clause....
					$orderBy  .= "CASE ";
					if(isset($myQueryCases1)!=""){
						$orderBy  .= "WHEN ".$nameAttribute." = '".$myQueryCases1."' THEN 1 ";
					}
					if(isset($myQueryCases2)!=""){
						$orderBy  .= "WHEN ".$nameAttribute." LIKE '".$myQueryCases2."' THEN 2 ";
					}
					if(isset($myQueryCases3)!=""){
						$orderBy  .= "WHEN ".$nameAttribute." LIKE '".$myQueryCases3."' THEN 3 ";
					}
					if(isset($myQueryCases4)!=""){
						$orderBy  .= "WHEN ".$nameAttribute." LIKE '".$myQueryCases4."' THEN 4 ";
					}
					
					//--- Algorithm to make all possible combinitions of Multi worded query..
					$a = $myURLSearchqueryArray;
					$len  = count($a);
					$list = array();

					for($i = 1; $i< (1 << $len); $i++) {
						$c = '';
						for($j = 0; $j < $len; $j++)
							if($i & (1 << $j))
								$c .= $a[$j]." ";
						$list[] = $c;
					}
					
					//--- Sort the Combinition Array on the basis of length...
					usort($list, function($a, $b) {
						return strlen($b) - strlen($a);
					});

					//echo "<pre>"; print_r($list);
					$caseCount = 5;
					$orderBy5 = " WHEN ";
					$orderBy6 = " WHEN ";
					$orderBy7 = " WHEN ";
					$wordCount = 0;
					foreach($list as $l){
						//
						$orderBy1 = " WHEN ";
						$orderBy2 = " WHEN ";
						$orderBy3 = " WHEN ";
						$orderBy4 = " WHEN ";
						
						$wordCount++;	
						if($wordCount==1 ){
							//$orderBy5 .= ' WHEN ';
							//echo 'zee-->WHEN name like "'.$myURLSearchquery.'%" THEN '.$caseCount++;die; 
							$orderBy5 .= $nameAttribute.' like "'.$myURLSearchqueryArray[0].'%" AND ';
							$orderBy7 .= $nameAttribute.' like "% '.$myURLSearchqueryArray[0].' %" AND ';
						}
						
						$termArray = explode(' ',trim($l));
						foreach($termArray as $ta){
							$orderBy1  .=  $nameAttribute.' LIKE "% '.$ta.' %" AND ';
							//$orderBy2  .= $nameAttribute.' LIKE "'.$ta.' %" AND ';
							//$orderBy3  .=  $nameAttribute.' LIKE " '.$ta.'%" AND ';
							
							//--- Single word ' WHOLE WORD ' CASE ... 
							if(count($termArray)==1){
								$orderBy .= ' WHEN '.$nameAttribute.' LIKE "% '.$ta.'"  OR '.$nameAttribute.' LIKE " '.$ta.'%" THEN '.$caseCount++;;
							}
							
							$orderBy4  .=  $nameAttribute.' LIKE "%'.$ta.'%"  AND ';
							
							if($wordCount==1 && $ta!=$myURLSearchqueryArray[0]){
								$orderBy5  .=  $nameAttribute.' like "% '.$ta.' %" AND ';
								$orderBy7  .=  $nameAttribute.' like "%'.$ta.'%" AND ';
							}
							if($wordCount==1 && $ta!=$myURLSearchqueryArray[count($myURLSearchqueryArray)-1]){
								$orderBy6 .=  $nameAttribute.'  like "% '.$ta.' %" AND ';
							}
						}
						
						$orderBy1 = substr($orderBy1,0,strlen($orderBy1)-5);
						//$orderBy2 = substr($orderBy2,0,strlen($orderBy2)-5);
						//$orderBy3 = substr($orderBy3,0,strlen($orderBy3)-5);
						
						
						
						$orderBy .= $orderBy1.' THEN '.$caseCount++;
						//$orderBy .= $orderBy2.' THEN '.$caseCount++;
						//$orderBy .= $orderBy3.' THEN '.$caseCount++;
						//$orderBy .= $orderBy4.' THEN '.$caseCount++;
						
						if($wordCount==1){
							$orderBy5 = substr($orderBy5,0,strlen($orderBy5)-4);
							//echo 'zee-->WHEN name like "'.$myURLSearchquery.'%" THEN '.$caseCount++;die; 
							$orderBy5 .= " THEN ".$caseCount++;
							$orderBy .= $orderBy5;
							
							$orderBy6 .=  $nameAttribute.'  like "% '.$myURLSearchqueryArray[count($myURLSearchqueryArray)-1].'" AND ';
							$orderBy6 = substr($orderBy6,0,strlen($orderBy6)-5);
							
							$orderBy6 .= " THEN ".$caseCount++;
							$orderBy  .= $orderBy6;
							
							$orderBy7 = substr($orderBy7,0,strlen($orderBy7)-4);
							//echo 'zee-->WHEN name like "'.$myURLSearchquery.'%" THEN '.$caseCount++;die; 
							$orderBy7 .= " THEN ".$caseCount++;
							$orderBy .= $orderBy7;
						}
							
						$orderBy4 = substr($orderBy4,0,strlen($orderBy4)-5);
						$orderBy .= $orderBy4.' THEN '.$caseCount++;
					}
					
					$orderBy  .= " ELSE ".$caseCount." END, ".$nameAttribute;
					
					//--- After getting Collection, Add Sorting Cases....
				}
				//--- DISABLED CLAUSE ATM...
		//$collection->addFieldToFilter('name',$searchParamsArray);
				if($myURLSearchOrder == "relevance" || !isset($myURLSearchOrder)){
						$collection->getSelect()->order(new Zend_Db_Expr($orderBy));
					}else{
						if($myURLSearchOrder == "price"){
							$tablePrefix = 'price_index';
						}elseif($myURLSearchOrder == "name"){
							$tablePrefix = $table;
						}elseif($myURLSearchOrder == "length"){
							$tablePrefix = $table;
						}elseif($myURLSearchOrder == "position"){
							$tablePrefix = 'catalog_category_product';
						} 
						
						//$myURLSearchOrderClause = $myURLSearchOrder == "position"?$myURLSearchOrder:$tablePrefix.'.'.$myURLSearchOrder;
						$myURLSearchOrderClause = $tablePrefix.'.'.$myURLSearchOrder;
						$myURLSearchOrderClause .= " ".$myURLSearchOrderDir;
						//$collection->getSelect()->order(new Zend_Db_Expr($myURLSearchOrderClause));
					}
			
			
		//echo $collection->getSelect();die;
		
		//$this->setProductCollection($collection);
        return $collection;
	}
	
	
}