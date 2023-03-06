<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Edited: M. zeeshan
 */

/**
 * Autocomplete queries list
 */
class Tejar_CatalogSearch_Block_Autocomplete extends Mage_CatalogSearch_Block_Autocomplete
{
    protected $_suggestData = null;

    protected function _toHtml()
    {
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $suggestData = $this->getSuggestData();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $isAjaxSuggestionCountResultsEnabled = (bool) Mage::app()->getStore()
            ->getConfig(Mage_CatalogSearch_Model_Query::XML_PATH_AJAX_SUGGESTION_COUNT);

        $count--;
		//--- Generate HTML	
        $html = '<ul id="myAutoComplete" class="myAutoComplete"><li style="display:none" class="highlight-search-row"></li>';
		if(isset($suggestData->products) && count($suggestData->products)){
		$html .= "<li class='search-title-label'><span>Products</span></li>";
        foreach ($suggestData->products as $index => $item) {
			
	/*--------------------------------------------------- ZEE CODE ------------------------------------------*/	
		$product = Mage::getModel('catalog/product')->load($item['id']);
	//--- Get Custom Stock Status if Avialable............
			$inStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock();
		
			$customInStockStatus = true;
			if($product->hasData('custom_stock_availability') || $product->getCustomStockAvailability()){
				if(Mage::helper('catalog/data')->customStockAddtoCartStatus($product)){
					$customInStockStatus = true;
				}else{
					$customInStockStatus = false;
				}
			}
		
			/* CONTINUE -  Check if Default or Custom In Stock Status is FALSE and if 'Display Out of Stock Products' Option 
			 * in Admin/Config Catalog Inventory is set to 'YES' Or 'NO'
			 */
			$displayOutOfStockConfigValue = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			if((!$inStock && !$displayOutOfStockConfigValue) || (!$customInStockStatus && !$displayOutOfStockConfigValue))continue;
	/*---------------------------------------------------- ZEE CODE -------------------------------------------*/	
			
		//--- Collecting all Attributes
			
			$short_desc = strip_tags($item['desc']);
			$short_desc = substr($short_desc, 0, 50);
			
			if(isset($item['price'])){
				$price      = strip_tags(Mage::helper('core')->currency($item['price']));
			}
			//--- Brand collection - URL and Name
			if(isset($item['price'])){
				$brandurl = Mage::getBaseUrl()."brand/".str_replace(' ', '-', $item['brand']);
				$brandName = $item['brand'];
			}
			//--- Brand collection - URL and Name END
			
			//--- Category Info collection 
			$category_ids = $item['category'];
			$categoryCount = count($item['category']);
			
			// To display lowest order category, Count the Path length of categories ..
			$pathIdsCountarray = array();
			foreach ($category_ids as $category_id) {
				$category = Mage::getModel('catalog/category')->load($category_id);
				$_path = $category->getPath();
				$ids = explode('/', $_path);
				//--- get path Ids...
				$pathIdsCountarray[] = $ids;
			}
			//--- To get Category ids for the path with maximum count (longest path), sort pathIdsCountarray in descending order
			rsort($pathIdsCountarray);
			foreach($pathIdsCountarray[0] as $childCat){
				$categoryPath = Mage::getModel('catalog/category')->load($childCat);
				//if(!isset($category_ids[$categoryIdIndex]) == $childCat	){
				if ($categoryPath->getIsActive()) {
					$catUrl  = $categoryPath->getUrl();
					$catName = $categoryPath->getName();
					//break;
				}
			}
				
			if($catName==""){
				$category = Mage::getModel('catalog/category')->load($category_ids[$categoryCount-1]);
				if ($category->getIsActive()) {
					$catUrl  = $category->getUrl();
					$catName = $category->getName();
				}	
			}	
			//--- Category Info collection END
			if ($index == 0) {
                $item['row_class'] .= ' first';
            }

            if ($index == $count) {
                $item['row_class'] .= ' last';
            }
			
				$html .=  '<li onclick="window.location =\''.$item['url'].'\'" title="' . $this->escapeHtml($item['title']) . '" class="search-item ' . $item['row_class'] . '">';
				if ($isAjaxSuggestionCountResultsEnabled) {
					$html .= '<span class="amount">' . $item['num_of_results'] . '</span>';
				}
				//$html .= "<div class='search-thumbnail'> ";
				//$html .= "<img  src='".$item['thumb'] ."'> ";
				//$html .= "</div>";
				
				$html .= "<div class='search-detail'> ";
				
				//--- Limit number of characters for titles inside AutoComplete Box...
					
					//$title = strlen($item['title'])<50?$item['title']:substr($item['title'], 0,65)."...";
					$titleWithCategory       = $item['title'].$catName;
					$titleWithCategoryLength = strlen($titleWithCategory)+7;
					
					//--- GET Current Theme(Desktop/Mobile/Tablet) Name based on Template...
					$currentTheme = Mage::getSingleton('core/design_package')->getTheme('template');
					if($currentTheme == "tejar-mobile"){
						$title = strlen( trim($item['title']))>=35 && $titleWithCategoryLength>=77?substr($item['title'], 0,35)."...":$item['title'];
					}elseif($currentTheme == "tejar-tablet"){
						$title = strlen( $item['title'])>=60 && $titleWithCategoryLength>=75?substr($item['title'], 0,60)."...":$item['title'];
					}elseif($currentTheme == "tejar"){
							//echo $titleWithCategoryLength;die;
						$title = strlen( $item['title'])>=80 && $titleWithCategoryLength>=90?substr($item['title'], 0,80)."...":$item['title'];
						
					}
				//--- Add Title HTML
				$html .= "<div class='search-title'> ";
				$html .= "<h2 class='name'>".$this->escapeHtml($title)."</h2>";
				
				//--- Add Category HTML
				$html .=  "<span style='display: inline-block !important;'>in <a href='".$catUrl."'>".$this->escapeHtml($catName)."</a></span>";
				$html .= "</div> ";
				//$html .= "<div class='search-other-detail'> ";
				
				//--- Add Brand HTML
			//	$html .=  "<span><strong>By:</strong> <a href='".$brandurl."'>".$this->escapeHtml($brandName)."</a></span>";
			
				//--- Add Model HTML
				
					//for configurable products: no Model available hence dont show...
				//	if($item['model']){
				//		$model = strip_tags($item['model']);
				//		$html .=  "<span style='display:inline-block; margin-left: 5px;'><strong>Model:</strong> ".$this->escapeHtml($model)."</span>";
				//	}
				//	$html .= "</div>";
				//	$html .= "<div class='search-price'>";
				
				//--- Add Price HTML
			//	$html .=  "<span><strong>Price:</strong> ".$this->escapeHtml($price)."</span>";
			//	$html .= "</div>";
				$html .= "</div>";
			
				//$html .= '<p style="clear:both;">'.$this->escapeHtml($short_desc) .'...</p>' ;
				$html .= '</li>';
			}
		}
	if(isset($suggestData->categories) && count($suggestData->categories)){
				
			$html .= "<li class='search-title-label'><span>Categories</span></li>";
			foreach ($suggestData->categories as $index => $item) {
				$catTitle = strlen($item['title'])<50?$item['title']:substr($item['title'], 0,45)."...";
				$catTitle = $this->escapeHtml($catTitle);
				//$catUrl   =  Mage::getBaseUrl().$item['url'];
				$html .=  '<li class="search-item" onclick="window.location =\''.$item['url'].'\'" title="' . $this->escapeHtml($item['title']) . '" class="' . $item['row_class'] . '">';
				
				 //--- Add Title HTML
				//$html .= "<pre><![CDATA[";
				$html .= "<div class='search-title'> ";
				$html .= "<h5 class='name' >".$catTitle."</h5>";
				$html .= "</div>";
				//$html .= "]]></pre>";
				$html .= "</li>";
			 }
		}
		//	$html .= '<li>';
			//$html .= '<span><strong><a style="font-size:14px;float:right" href="javascript:void(0)" onclick="jQuery(\'#search_mini_form\').submit()">View More..</a><strong></span>';
		//	$html .= "</li>";
			$html.= '</ul>';
		//--- Generate HTML END	
        return $html;
    }

    public function getSuggestData()
    { 
        if (!$this->_suggestData) {
            $collection = $this->helper('catalogsearch')->getSuggestCollection();
			//var_dump($collection);
            $collectionCategory = isset($collection[0])?$collection[0]:null;
            $collectionProducts = isset($collection[1])?$collection[1]:null;
            $collectionModel    = isset($collection[2])?$collection[2]:null;
			
			$collectionProductsCount = count($collectionProducts);
			$collectionCategoryCount = count($collectionCategory);
			$collectionModelCount = count($collectionModel);
			
			if($collectionProductsCount==0 || $collectionModelCount>0){
				$collectionProductsCount = $collectionProductsCount;
			}
			
			if($collectionProductsCount>0  || $collectionModelCount>0){
				$counter = 0;
				$data    = array();
				$query   = $this->helper('catalogsearch')->getQueryText();
				
				//--- initialize an object array for $this->_suggestData to return & hold search data...
				$this->_suggestData = (object) array();
				foreach ($collectionModel as $item) {
					$asscProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());
					//var_dump($path); die;
					//if($item->getResourceName()!== 'catalog/category'){
					$_data = array(
						'type'           => $item->getTypeId(),
						'title'          => $item->getQueryText(),
						//'thumb'          => $item->getThumbnailUrl(),
						//'brand'          => $item->getAttributeText('manufacturer'),
						//'brandId'        => $item->getManufacturer(),
						'category'       => $item->getCategoryIds(),
						//'model'          => $item->getModel(),
						//'price'          => $item->getPrice(),
						'desc'           => $item->getShortDescription(),
						'url'            => $asscProduct->getProductUrl(),
						'row_class'      => (++$counter)%2?'odd':'even',
						'num_of_results' => $item->getNumResults(),
						'id'             => $item->getId(),
					);
					//$urlProductId =  $item->getId();
					//$lastCatId = $item->getCategoryIds()[count( $item->getCategoryIds())-1];
					//--- If, as in simple/associated products, no categories were found then use 
					//--- the main product categories..
					//echo count($_data['category']);die;
					if(count($_data['category'])==0){
						$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
							->getParentIdsByChild($_data['id']);
							$_product = Mage::getModel('catalog/product')->load($parentIds[0]);
							$catIds = $_product->getCategoryIds();
							//var_dump($ids);die;
							$_data['category'] = $catIds;
							//$urlProductId = $parentIds[0];
							//$lastCatId = $catIds[count( $catIds)-1];
					}
					
						if ($item->getQueryText() == $query) {
								array_unshift($data, $_data);
							}
							else {
								$data[] = $_data;
							}
					//$this->_suggestData->products = array();
					$this->_suggestData->products  = $data;
				}
				
			if(isset($collectionProducts) && $collectionProducts->getSize()){
				foreach ($collectionProducts as $item) {
					//var_dump($path); die;
					//if($item->getResourceName()!== 'catalog/category'){
					$_data = array(
						'type'           => $item->getTypeId(),
						'title'          => $item->getQueryText(),
						//'thumb'          => $item->getThumbnailUrl(),
						//'brand'          => $item->getAttributeText('manufacturer'),
						//'brandId'        => $item->getManufacturer(),
						'category'       => $item->getCategoryIds(),
						//'model'          => $item->getModel(),
						//'price'          => $item->getPrice(),
						'desc'           => $item->getShortDescription(),
						'url'            => $item->getProductUrl(),
						'row_class'      => (++$counter)%2?'odd':'even',
						'num_of_results' => $item->getNumResults(),
						'id'             => $item->getId(),
					);
					//$urlProductId =  $item->getId();
					//$lastCatId = $item->getCategoryIds()[count( $item->getCategoryIds())-1];
					//--- If, as in simple/associated products, no categories were found then use 
					//--- the main product categories..
					//echo count($_data['category']);die;
					if(count($_data['category'])==0){
						$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
							->getParentIdsByChild($_data['id']);
							$_product = Mage::getModel('catalog/product')->load($parentIds[0]);
							$catIds = $_product->getCategoryIds();
							//var_dump($ids);die;
							$_data['category'] = $catIds;
							//$urlProductId = $parentIds[0];
							//$lastCatId = $catIds[count( $catIds)-1];
					}
						
						if ($item->getQueryText() == $query) {
								array_unshift($data, $_data);
							}
							else {
								$data[] = $_data;
							}
						//$this->_suggestData->products = array();
						$this->_suggestData->products  = $data;
					}
				}
			}
			if($collectionCategoryCount>0){
				$counter = 0;
				$data = array();
				$query = $this->helper('catalogsearch')->getQueryText();
				foreach ($collectionCategory as $cat) {
					//var_dump($item->getResourceName());
					if ($cat->getProductCount()) {
						//if($item->getResourceName()!== 'catalog/category'){
						$_dataCat = array(
							'title'          => $cat->getName(),
							'thumb'          => $cat->getThumbnailUrl(),
							'desc'           => $cat->getShortDescription(),
							'url'            => $cat->getUrl(),
							'row_class'      => (++$counter)%2?'odd':'even',
							'num_of_results' => $cat->getNumResults(),
						);
						//}
						if ($cat->getQueryText() == $query) {
								array_unshift($data, $_dataCat);
							}
							else {
								$data[] = $_dataCat;
							}
						$this->_suggestData->categories = $data;
					}
				}
			}
        }
        return $this->_suggestData;
    }
}