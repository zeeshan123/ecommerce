<?php 

class Tejar_Alternate_Model_Observer
{
	public function alternateLinks(){
		$headBlock = Mage::app()->getLayout()->getBlock('head');
		$stores = Mage::app()->getStores();
		$prod = Mage::registry('current_product');
		$categ = Mage::registry('current_category');
	 
		if($headBlock){
				if(Mage::helper('tejar_alternate')->isCanonical()){
				$headBlock->addLinkRel('canonical', $url);
			}
			foreach ($stores as $store){
				if($prod){
					$categ ? $categId=$categ->getId() : $categId = null;
					$url = $store->getBaseUrl() .$prod->getUrlKey();
				 //$url = $store->getBaseUrl() . Mage::helper('tejar_alternate')->rewrittenProductUrl($prod->getId(), $categId, $store->getId());
				}elseif($categ){
				   $urlSuffix = rtrim(str_replace(Mage::getBaseUrl(),'',Mage::helper('core/url')->getCurrentUrl()),'/');
					$url = $store->getBaseUrl().$urlSuffix;
				}else{
				   $url =  $store->getBaseUrl();
				   $urlSuffix = rtrim(str_replace(Mage::getBaseUrl(),'',Mage::helper('core/url')->getCurrentUrl()),'/');
				   //$urlSuffix = Mage::helper('tejar_alternate')->getPageDefaultUrlKey();
				   $currentModuleName = Mage::app()->getFrontController()->getRequest()->getModuleName();
					if($store->getCode()=='pk'){
						//echo "---> ".Mage::getBaseUrl();die;
					}
					//if(strtolower($currentModuleName)=="cms"){
						$url = $url.$urlSuffix;
					//}
			    }
				$storeCode = substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
				if($store->getCode() != "default"){
					$storeCode = $storeCode."-".$store->getCode();   
				}
				$headBlock->addLinkRelThis('alternate"' . ' hreflang="' . $storeCode, $url);
			}
		
			//echo $configValue;
			//$headBlock->addLinkRel('canonical"' . ' hreflang="' . "ddd", $url);
		}
	   return $this;
	}
}