<?php
//require_once 'Mage/CatalogSearch/controllers/ResultController.php';
class Tejar_CustomFilters_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    { 
		//--- Redirect to 404 if 'index' is included within the URL: to avoid SEO issues ;
		$currURL =  Mage::helper('core/url')->getCurrentUrl(); 
		if(stristr($currURL, '/index')){
				$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
				$this->getResponse()->setHeader('Status','404 File not found');
				$this->_forward('defaultNoRoute');
		}else{
		//echo "ZEE----> ";die;
			$this->loadLayout();
			
				// apply custom ajax layout
				if ($this->getRequest()->isAjax()) {
					$update = $this->getLayout()->getUpdate();
					$update->addHandle('catalog_category_layered_ajax_layer');
				}
				$this->_initLayoutMessages('catalog/session');
				$this->_initLayoutMessages('checkout/session');
	//--- SET Document Title

			$pageTitleAjax = "";
			$pageKeywordsAjax = "";
			$pageDescriptionAjax = "";
			$_linksArray = @unserialize(Mage::getStoreConfig('intenso/header/main_menu_links', Mage::app()->getStore()));
			
					$currentModuleName = Mage::app()->getFrontController()->getRequest()->getModuleName();
					$titleSeparator  = " - ";
					$prefixToAttachToTitle = "";

						switch($currentModuleName){
							case "deals":
								$pageTitleAjax = Mage::getStoreConfig('intenso/deals_page/deals_title', Mage::app()->getStore());
								$pageKeywordsAjax = Mage::getStoreConfig('intenso/deals_page/deals_keywords', Mage::app()->getStore());
								$pageDescriptionAjax = Mage::getStoreConfig('intenso/deals_page/deals_description', Mage::app()->getStore());
							break;
							case "new-arrival":
								$pageTitleAjax = Mage::getStoreConfig('intenso/newarrival_page/newarrival_title', Mage::app()->getStore());
								$pageKeywordsAjax = Mage::getStoreConfig('intenso/newarrival_page/newarrival_keywords', Mage::app()->getStore());
								$pageDescriptionAjax = Mage::getStoreConfig('intenso/newarrival_page/newarrival_description', Mage::app()->getStore());
							break;
							case "best-seller":
								$pageTitleAjax = Mage::getStoreConfig('intenso/bestseller_page/bestseller_title', Mage::app()->getStore());
								$pageKeywordsAjax = Mage::getStoreConfig('intenso/bestseller_page/bestseller_keywords', Mage::app()->getStore());
								$pageDescriptionAjax = Mage::getStoreConfig('intenso/bestseller_page/bestseller_description', Mage::app()->getStore());
							break;
							case "most-viewed":
								$pageTitleAjax = Mage::getStoreConfig('intenso/mostviewed_page/mostviewed_title', Mage::app()->getStore());
								$pageKeywordsAjax = Mage::getStoreConfig('intenso/mostviewed_page/mostviewed_keywords', Mage::app()->getStore());
								$pageDescriptionAjax = Mage::getStoreConfig('intenso/mostviewed_page/mostviewed_description', Mage::app()->getStore());
							break;
							case "featured":
								$pageTitleAjax = Mage::getStoreConfig('intenso/featured_page/featured_title', Mage::app()->getStore());
								$pageKeywordsAjax = Mage::getStoreConfig('intenso/featured_page/featured_keywords', Mage::app()->getStore());
								$pageDescriptionAjax = Mage::getStoreConfig('intenso/featured_page/featured_description', Mage::app()->getStore());
							break;
						}
						
						if($pageTitleAjax == ""){
							if($_linksArray){
								foreach ($_linksArray as $menuItem){
									if($currentModuleName == $menuItem['url']){
										$pageTitleAjax = $menuItem['menu_item']; 	
									} 
								}
							}
						}
						
						
				// return json formatted response for ajax
				if($this->getRequest()->isAjax()){
					$listing = $this->getLayout()->getBlock('new_product')->toHtml();
					$layer = $this->getLayout()->getBlock('catalog.leftnav')->toHtml();
				   
					// fix urls that contain '___SID=U'
					$urlModel = Mage::getSingleton('core/url');
					$listing  = $urlModel->sessionUrlVar($listing);
					$layer    = $urlModel->sessionUrlVar($layer);

					// get query text
					//$searchQuery = Mage::helper('catalogsearch')->getQueryText();
					
					$catalogLayer = Mage::getSingleton('tejar_customfilters/layer');
					
					$appliedFilters = $catalogLayer->getState()->getFilters();
					$appliedFiltersCount = 0;
					foreach ($appliedFilters as $item) {
						$appliedFiltersCount ++;
					}

				   // link to clear all filters
					//$clearURL =  Mage::helper('tejar_customfilters')->getClearFiltersUrl();
					$clearURL =  Mage::getUrl().Mage::app()->getFrontController()->getRequest()->getModuleName();
					$clearLink = '';
					if ($appliedFiltersCount > 0) {
						$clearLink = '<a href="'.$clearURL.'" class="filter-reset">'. $this->__('Reset Filters') .'</a>';
					}

					// amount
					$lastPageNum = $catalogLayer->getProductCollection()->getLastPageNumber();
					$size = $catalogLayer->getProductCollection()->getSize();
					if ($lastPageNum > 1) {
						$curPage = $catalogLayer->getProductCollection()->getCurPage();
						$count = $catalogLayer->getProductCollection()->count();
						$limit = $catalogLayer->getProductCollection()->getPageSize();
						$firstNum = $limit * ($curPage - 1) + 1;
						$lastNum = $limit * ($curPage - 1) + $count;
						$amount = $this->__('Items %s to %s of %s total', $firstNum, $lastNum, $size);
					} else {
						$amount = '<strong>'. $this->__('%s Item(s)', $size) . '</strong>';
					}
					
					// toolbar pager
					$toolbar= Mage::getBlockSingleton('catalog/product_list')->getToolbarBlock()->setTemplate('catalog/product/list/pager.phtml');
					$toolbar->setCollection($catalogLayer->getProductCollection());
					$pager = $this->getLayout()->getBlock('product_list_toolbar_pager');
					$toolbar->setChild('product_list_toolbar_pager', $pager);

					// orders
					$toolbarSingleton = Mage::getBlockSingleton('tejar_customfilters/product_list_toolbar');
					$availableOrders = $toolbarSingleton->getAvailableOrders();
					$orders = '';
				
					foreach ($availableOrders as $_key=>$_order) {
							
						// ascending order
						$orders .= '<option value="'. $toolbarSingleton->getOrderUrl($_key, 'asc') .'"';
						if ($toolbarSingleton->isOrderCurrent($_key)) {
							$orders .= ' selected="selected">';
						} else {
							$orders .= '>';
						}
						$orders .= $this->__($_order) . ' ' . $this->__('asc.') . '</option>';
						// descending order
						$orders .= '<option value="'. $toolbarSingleton->getOrderUrl($_key, 'desc') .'"';
						if ($toolbarSingleton->isOrderCurrent($_key)) {
							$orders .= ' selected="selected">';
						} else {
							$orders .= '>';
						}
						$orders .= $this->__($_order) . ' ' . $this->__('desc.') . '</option>';
					}
		
					// limits
					$availableLimit = $toolbarSingleton->getAvailableLimit();
					$limits = '';
					foreach ($availableLimit as $_key=>$_limit) {
						$limits .= '<option value="'. $toolbarSingleton->getLimitUrl($_key) .'"';
						if ($toolbarSingleton->isLimitCurrent($_key)) {
							$limits .= ' selected="selected">';
						} else {
							$limits .= '>';
						}
						$limits .= $this->__($_limit) . ' ' . $this->__('items per page') . '</option>';
					}
					//echo  $toolbar->toHtml();die;
					$response = array(
						'listing'      => $listing,
						'layer'        => $layer,
						'categoryName' => $pageTitleAjax,
						'clearLink'    => $clearLink,
						'amount'       => $amount,
						'pager'        => $toolbar->toHtml(),
						'orders'       => $orders,
						'limits'       => $limits
					);

					$this->getResponse()->setHeader('Content-Type', 'application/json', true);
					$this->getResponse()->setBody(json_encode($response));
				}else{
					if($pageTitleAjax){
						$this->getLayout()->getBlock('head')->setTitle($this->__($pageTitleAjax));	
					}
					if($pageKeywordsAjax){
						$this->getLayout()->getBlock('head')->setKeywords($this->__($pageKeywordsAjax));	
					}
					if($pageDescriptionAjax){
						$this->getLayout()->getBlock('head')->setDescription($this->__($pageDescriptionAjax));
					}
					$this->renderLayout();
				}
		}
    }
}