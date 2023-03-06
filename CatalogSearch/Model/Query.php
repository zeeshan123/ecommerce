<?php
/*
 * @category    Tejar
 * @package     Tejar_CatalogSearch
 * @author      Zeeshan <zeeshan.zeeshan123@gmail.com>
 */
class Tejar_CatalogSearch_Model_Query extends Mage_CatalogSearch_Model_Query
{
    
	/**
     * Retrieve collection of suggest queries
     *
     * @return Mage_CatalogSearch_Model_Resource_Query_Collection
     */
	public function getSuggestCollection(){
		
		//Mage::app()->getStore()->setConfig(Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT, '0');
		//--- Initialize all values...
		$returnCollection   = array();
		$searchParamsArray  = array();
		$myURLSearchquery   = $this->getQueryText();
		$myQueryCases1      = "";
		$myQueryCases2      = "";
		$myQueryCases3      = "";
		$myQueryCases4      = "";
		$myQueryCases5 		= "";
		$myQueryCases6 		= "";
		$myQueryCases7 		= "";
		$myQueryCases8 		= "";
		$orderBy            = "";
		$myQueryRegex       = "";
		//echo $myURLSearchquery;die;
		//--- Escape string for multiple spaces or new lines.
		$myURLSearchquery = trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $myURLSearchquery));
		$myURLSearchquery = addslashes($myURLSearchquery);
		//--- Check if search query posses spaces i.e. is single or multiple words..
			if(strpos($myURLSearchquery, ' ')){
				$myURLSearchqueryArray = explode(' ', $myURLSearchquery);
				$wordIndex = 0;
				//--- Generate CASES for ORDER BY clause for multiple words 
				$myQueryCases1 .= $myURLSearchquery;
				$myQueryCases2 .= "% ".$myURLSearchquery;
				$myQueryCases3 .= $myURLSearchquery." %";
				$myQueryCases4 .= "% ".$myURLSearchquery." %";
				$myQueryCases8 .= "%".$myURLSearchquery."%";
				$myQueryRegex  .= "";
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
					
				}
				
				array_push($searchParamsArray, array('like' =>  $myURLSearchquery[0]. $myURLSearchquery[1]."_%"));
			
			}else{
				
				if(isset($myURLSearchquery[strlen($myURLSearchquery)-1]) && $myURLSearchquery[strlen($myURLSearchquery)-1]!='s'){
					$considerPlural = "s";
				}elseif($myURLSearchquery[strlen($myURLSearchquery)-1]=='s'){
					
					$considerPlural = "";
					array_push($searchParamsArray, array('like' =>  "% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %"));
					array_push($searchParamsArray, array('like' =>  " ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)."%"));
					array_push($searchParamsArray, array('like' =>  "%".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)));
				}
				if(isset($myURLSearchquery[strlen($myURLSearchquery)-1])){
					array_push($searchParamsArray, array('regexp' =>  "^_".$myURLSearchquery[0].".*".$myURLSearchquery[strlen($myURLSearchquery)-1]."_$"));
				}
				array_push($searchParamsArray, array('like' =>  $myURLSearchquery."%"));
				array_push($searchParamsArray, array('like' =>  "%"." ".$myURLSearchquery." "."%")); 
				array_push($searchParamsArray, array('like' =>  "%_ ".$myURLSearchquery."%"));
				array_push($searchParamsArray, array('like' =>  "%_".$myURLSearchquery));
				array_push($searchParamsArray, array('like' =>  "%_".$myURLSearchquery." %"));
				array_push($searchParamsArray, array('like' =>  "%_".$myURLSearchquery.$considerPlural." _%"));
				array_push($searchParamsArray, array('like' =>  "%".$myURLSearchquery.$considerPlural."%"));
				array_push($searchParamsArray, array('like' =>  "%".$myURLSearchquery.$considerPlural));
				array_push($searchParamsArray, array('eq' => $myURLSearchquery));
			}
		
		//--- Serch by Models collection
			//---Check if model attribute exists, search only by SKU if not
			$entity = 'catalog_product';
			$code = 'model';
			$attr = Mage::getResourceModel('catalog/eav_attribute')
				->loadByCode($entity,$code);
			if ($attr->getId()) {
				$collectionModel = Mage::getResourceModel('catalog/product_collection');
				$collectionModel->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
				$collectionModel->addAttributeToFilter(
				array(
					array('attribute'=>'model','=' => $myURLSearchquery)
					)
				);
				
				$collectionModel->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
				->setPageSize(6)
				->addAttributeToFilter('status',array('eq' => '1'))
				->addAttributeToFilter('visibility', array('neq' => 1)) //--- Not visible individually
				->addAttributeToFilter('visibility', array('neq' => 2)) //--- Only catalog visibility
				->addStoreFilter($this->getStoreId());
			}
			
		//--- Search by SKU collection
		if(!$collectionModel->getSize()){
			$collectionModel = Mage::getResourceModel('catalog/product_collection');
			$collectionModel->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price','sku', 'thumbnail', 'short_description', 'url_key'));
			$collectionModel->addAttributeToFilter(
			array(
				array('attribute'=>'sku','=' => $myURLSearchquery)
				)
			);
			
			$collectionModel->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
			->setPageSize(6)
			->addAttributeToFilter('status',array('eq' => '1'))
			->addAttributeToFilter('visibility', array('neq' => 1)) //--- Not visible individually
			->addAttributeToFilter('visibility', array('neq' => 2)) //--- Only catalog visibility
			->addStoreFilter($this->getStoreId());
		}
		
		if(!$collectionModel->getSize()){
		//--- Products Collection 
			$collection = Mage::getResourceModel('catalog/product_collection');
		//--- Add visibility Filter to Product Collection to display Configurable and Simple product only..
			//Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
			if(($key = array_search("%".$myURLSearchquery."%", $searchParamsArray)) !== false) {
					unset($searchParamsArray[$key]);
				}
				//Again check if search query contains multiple words!
				if(!strpos($myURLSearchquery, ' ')){
					//--- if search query contains single word, prepare Cases Order Clause..
					$orderBy  .= "CASE WHEN name LIKE '".$myURLSearchquery."' THEN 1 WHEN name LIKE '".$myURLSearchquery." %'  THEN 2 WHEN name LIKE '% ".$myURLSearchquery." %' THEN 3 WHEN name LIKE '% ".$myURLSearchquery."' THEN 4 WHEN name LIKE '".$myURLSearchquery."_%' THEN 5 WHEN name LIKE '%".$myURLSearchquery."%' THEN 6 WHEN name LIKE '".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %' THEN 7 WHEN name LIKE '% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)." %' THEN 8 WHEN name LIKE '% ".substr($myURLSearchquery, 0, strlen($myURLSearchquery)-1)."' THEN 9 ELSE 10 END, name";
					
					$collection->getSelect()->order(new Zend_Db_Expr ($orderBy));
				}else{
					
					//---if search query contains multiple words, prepare Cases For ORDER BY Clause....
					$orderBy  .= "CASE ";
					if(isset($myQueryCases1)!=""){
						$orderBy  .= "WHEN name = '".$myQueryCases1."' THEN 1 ";
					}
					if(isset($myQueryCases2)!=""){
						$orderBy  .= "WHEN name LIKE '".$myQueryCases2."' THEN 2 ";
					}
					if(isset($myQueryCases3)!=""){
						$orderBy  .= "WHEN name LIKE '".$myQueryCases3."' THEN 3 ";
					}
					if(isset($myQueryCases4)!=""){
						$orderBy  .= "WHEN name LIKE '".$myQueryCases4."' THEN 4 ";
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
						$orderBy1 = " WHEN";
						$orderBy2 = " WHEN";
						$orderBy3 = " WHEN";
						$orderBy4 = " WHEN";
						
						$wordCount++;	
						if($wordCount==1 ){
							//$orderBy5 .= ' WHEN ';
							//echo 'zee-->WHEN name like "'.$myURLSearchquery.'%" THEN '.$caseCount++;die; 
							$orderBy5 .= 'name like "'.$myURLSearchqueryArray[0].'%" AND ';
							$orderBy7 .= 'name like "% '.$myURLSearchqueryArray[0].' %" AND ';
						}
						
						$termArray = explode(' ',trim($l));
						foreach($termArray as $ta){
							$orderBy1  .=  ' name LIKE "% '.$ta.' %" AND ';
							//$orderBy2  .=  ' name LIKE "'.$ta.' %" AND ';
							//$orderBy3  .=  ' name LIKE " '.$ta.'%" AND ';
							
							//--- Single word ' WHOLE WORD ' CASE ... 
							if(count($termArray)==1){
								$orderBy .= ' WHEN name LIKE "% '.$ta.'"  OR name LIKE " '.$ta.'%" THEN '.$caseCount++;;
							}
							
							$orderBy4  .=  ' name LIKE "%'.$ta.'%"  AND ';
							
							if($wordCount==1 && $ta!=$myURLSearchqueryArray[0]){
								$orderBy5  .=  ' name like "% '.$ta.' %" AND ';
								$orderBy7  .=  ' name like "%'.$ta.'%" AND ';
							}
							if($wordCount==1 && $ta!=$myURLSearchqueryArray[count($myURLSearchqueryArray)-1]){
								$orderBy6 .=  ' name like "% '.$ta.' %" AND ';
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
							
							$orderBy6 .=  ' name like "% '.$myURLSearchqueryArray[count($myURLSearchqueryArray)-1].'" AND ';
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
					
					$orderBy  .= " ELSE ".$caseCount." END, name";
					
					//--- After getting Collection, Add Sorting Cases....
					
					$collection->getSelect()->order(new Zend_Db_Expr($orderBy));
				}
					 
			$collection->addAttributeToSelect(array('name', 'url_path', 'category_id', 'manufacturer', 'price', 'model', 'thumbnail', 'short_description', 'url_key'))
			->addAttributeToFilter('name',$searchParamsArray)
			//--- To display Configurable or Associated Products..
			/*->joinTable('catalog/product_relation', 'child_id=entity_id', array(
            'parent_id' => 'parent_id'
					), null, 'left')
					->addAttributeToFilter(array(
						array(
							'attribute' => 'parent_id',
							'null' => null
						)
					))*/
			//->addAttributeToFilter('type_id', array('eq' => 'configurable'))
			->addAttributeToFilter('status', array('eq' => 1))
			->addAttributeToFilter('visibility', array('neq' => 1)) //--- Not visible individually
			->addAttributeToFilter('visibility', array('neq' => 2)) //--- Only catalog visibility
			->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
			->addAttributeToSort('name', 'ASC')
			->setPageSize(6)
			->addStoreFilter($this->getStoreId());
		
		//echo $collection->getSelect();die;
		
			}
		
		//--- If both collections are null, check for associate's configurable product and display it..
		if((!isset($collection) || (isset($collection) && !$collection->getSize())) && !$collectionModel->getSize()){
			
			$collectionModel = Mage::getResourceModel('catalog/product_collection');
			$collectionModel->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
			
			//---Check if model attribute exists, search only by SKU if not
			$entity = 'catalog_product';
			$code = 'model';
			$attr = Mage::getResourceModel('catalog/eav_attribute')
				->loadByCode($entity,$code);
			if ($attr->getId()) {
				$collectionModel->addAttributeToFilter(
				array(
					array('attribute'=>'model','=' => $myURLSearchquery),
					array('attribute'=>'sku','=' => $myURLSearchquery)
					)
				);
			}else{
				$collectionModel->addAttributeToFilter(
				array(
					//array('attribute'=>'model','=' => $myURLSearchquery),
					array('attribute'=>'sku','=' => $myURLSearchquery)
					)
				);
			}
			
			//->addAttributeToFilter('sku', array('like' => $myURLSearchquery."%"))
			$collectionModel->addExpressionAttributeToSelect('query_text', '{{name}}', 'name')
			->setPageSize(1)
			->addStoreFilter($this->getStoreId());
			
			//echo $collectionModel->getSize();die;
			foreach($collectionModel as $item){
				$parentProduct =   Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId());
			
			}
			if(isset($parentProduct) && isset($parentProduct[0])){
				$collectionModel = Mage::getResourceModel('catalog/product_collection');
				$collectionModel->addAttributeToSelect(array('name', 'category_id', 'url_path', 'manufacturer', 'price', 'model','sku', 'thumbnail', 'short_description', 'url_key'));
				$collectionModel->addExpressionAttributeToSelect('query_text', '{{name}}', 'name');
				$collectionModel->addFieldToFilter('entity_id',array('eq'=>$parentProduct[0]))
				//->addAttributeToSort('model', 'ASC')
				->setPageSize(6);
			}
			
		}
		
	
	//--- When Using Flat Catalog...
		$collectionCategory = Mage::getResourceModel('catalog/category_flat_collection'); 
		$collectionCategory = $collectionCategory
                   // ->load()
                    ->addAttributeToSelect(array('name', 'image', 'description'))
					->setPageSize(4)
					->addFieldToFilter('name', $searchParamsArray);
					
		$collectionCategory->getSelect()->order(new Zend_Db_Expr($orderBy));
		//echo $collectionCategory->getSelect();die;
			
			if(isset($collectionCategory)){
				$returnCollection[0] = 	$collectionCategory;
			}
			if(isset($collection)){
				$returnCollection[1] = 	$collection;
			}
			if(isset($collectionModel)){
				$returnCollection[2] = 	$collectionModel;
			}
			//echo $collectionCategory->getSelect();die;
		 //$this->setProductCollection($collection);	
		return $returnCollection;
	}
  
}
