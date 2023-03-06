<?php
/**
 * ObservPricing Observer 
 *
 * @category   Tejar
 * @package    Tejar_ObservPricing
 * @class      Tejar_Observpricing_Model_Observer
 * @author     Zeeshan <zeeshan.zeeshan123@gmail.com>
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tejar_Observpricing_Model_Observer extends Varien_Event_Observer
{
	/*
	*@name        productSaveAfter
	*@description This Observer function calls another class function to return simple product price..
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function simpleProductPrice(Varien_Event_Observer $observer){
		
		$event   = $observer->getEvent();
		$product = $event->getProduct();
		$qty     = $event->getQty();
		
		if($product->getTypeId()=="simple"){
			return false;
		}
		//Mage::log($observer, null, 'confPricing.log');
		//--- process percentage discounts only for simple products
		$selectedAttributes = array();
		if ($product->getCustomOption('attributes')) {
			//Mage::log('yes-----', null, 'confPricing.log');
			$selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
		}
		if (sizeof($selectedAttributes)) return $this->getSimpleProductPrice($qty, $product);
	}
	
	/*
	*@name        getSimpleProductPrice
	*@description This function is called by the observer function simpleProductPrice
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function getSimpleProductPrice($qty=null, $product){
        $cfgId = $product->getId();
        $product->getTypeInstance(true)
            ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbMeta = Mage::getSingleton('core/resource');
        $sql = <<<SQL
SELECT main_table.entity_id FROM {$dbMeta->getTableName('catalog/product')} `main_table` INNER JOIN
{$dbMeta->getTableName('catalog/product_super_link')} `sl` ON sl.parent_id = {$cfgId}
SQL;
        foreach($selectedAttributes as $attributeId => $optionId) {
            $alias = "a{$attributeId}";
            $sql .= ' INNER JOIN ' . $dbMeta->getTableName('catalog/product') . "_int" . " $alias ON $alias.entity_id = main_table.entity_id AND $alias.attribute_id = $attributeId AND $alias.value = $optionId AND $alias.entity_id = sl.product_id";
        }
        $id = $db->fetchOne($sql);
		//Mage::log(Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty), null, 'confPricing.log');
        //return 
		$fp = Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty);
		return $product->setFinalPrice($fp);
    }
	
	/*
	*@name        catalogProductPrepareSave
	*@description This Observer function returns function after changing the SKU, setting stock status 
				  and trimming name for whitespaces of currently inserted Product..
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function catalogProductPrepareSave(Varien_Event_Observer $observer){
			
		$event   = $observer->getEvent();
		$product = $event->getProduct();
		$productCustomStockStatus = $product->getAttributeText('custom_stock_availability');
		
		//--- GET Default Store ID..
		$defaultStoreId = Mage::app()->getWebsite()->getDefaultGroup()->getDefaultStoreId();
		
		$storeId = $observer->getEvent()->getProduct()->getStoreId();
		
		
		//$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product->getId());
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$prodOrigData = $product->getOrigData();
		//echo $prodOrigData['stock_item']['use_config_manage_stock'] .' == '. $product->getStockData('use_config_manage_stock'); die;
		//--- Check Product Original Data First..
		if($prodOrigData['stock_item']['use_config_manage_stock']!== null && trim($prodOrigData['stock_item']['use_config_manage_stock']) !== trim($product->getStockData('use_config_manage_stock'))){
			return $product->setCustomStockAvailability('');
		}
		
		//--- Set Stock Status in accordance with Custom Stock...
		if($product->getStockData('is_in_stock') && $productCustomStockStatus=="No Longer Available" || $productCustomStockStatus=="Discontinued"){
			$product->setStockData(array( 
            'use_config_manage_stock' => 0,
            'is_in_stock' => 0, 
            'qty' => 0,
            'manage_stock' => 1,
            'use_config_notify_stock_qty' => 0
			)); 
		}elseif($product->getStockData('is_in_stock')==0 && $productCustomStockStatus=="Pre-order" || $productCustomStockStatus=="Backordered"){
			$product->setStockData(array( 
            'use_config_manage_stock' => 1,
            //'is_in_stock' => 0, 
            //'qty' => 0,
            'manage_stock' => 0,
            'use_config_notify_stock_qty' => 1
			)); 
		}
		//$product->getResource()->save($product);
		$product->save();
	}
	
	public function setDefaultSkuAndStock($product, $defaultStoreId){
		//--- Trim Product name to avoid Whitespaces issues..
		$product->setName(trim($product->getName()));

		//--- Check if SKU is already set, Insert only if there is no SKU...
		if(!$product->getSku()){
			$product->setSku(Mage::helper('tejarobservpricing')->getRandomSKU())->save();
			//$product->getResource()->save($product);
		}
	}
	
	/*
	*@name        isCustomCostAvailable
	*@description This Observer function set prices per store based for currently inserted product..
	*@parameters  varchar StoreId 
	*@returns     boolean
	*/
	public function isCustomCostAvailable($storeId){
		//--- Get Store Based Custom Cost and Custom Shipping Cost...
		$customCost = Mage::getStoreConfig('tejarobservpricing/store_custom_cost/custom_cost',$storeId); 
		$customShippingCost = Mage::getStoreConfig('tejarobservpricing/store_custom_cost/custom_shipping_cost',$storeId); 
		
		//--- if Custom Cost or Custom Shipping Cost is not available return false..
		if(!$customCost || !$customShippingCost){
			return false;
		}
		return true;
	}
	
	/*
	*@name        catalogProductSaveAfter
	*@description This Observer function set prices per store based for currently inserted product..
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function catalogProductSaveAfter(Varien_Event_Observer $observer){
		
		$event     = $observer->getEvent();
		$product   = $event->getProduct();
		$productId = $product->getId();
		
		//--- GET Default Store ID..
		$defaultStoreId = Mage::app()->getWebsite()->getDefaultGroup()->getDefaultStoreId();
		
		//--- Set default product SKU and Stock Status & trim name for whitespaces
			
		//--- GET Base USD Price/Rate & Special Price...
		$productDefault   = Mage::getModel('catalog/product')->setStoreId($defaultStoreId)->load($productId);
		$usd_rate         = $productDefault->getPrice();
		$usd_specialPrice = $productDefault->getspecialPrice();
		
		//--- Set Product SKU
		$this->setDefaultSkuAndStock($productDefault, $defaultStoreId);
		
		//--- Check if Custom cost attributes are available if not return i.e. don't do anything to prices..
		if(!$this->isCustomCostAvailable($defaultStoreId)){
			//return;
		}
		//--- Loop through available Stores...	
		foreach(Mage::app()->getStores() as $store){
			
			$storeCurrencyCode = $store->getCurrentCurrencyCode();
			
			if($storeCurrencyCode == "USD"){
				//continue ; 
			}
			
			$storeId           = $store->getId();
			
			if($storeId == $defaultStoreId){
				continue;
			}
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
			
			//--- Get Meta Title and Meta Descriptipon for current Product...
			$metaTitle = Mage::helper('tejarobservpricing')->getMetaTitle($storeCurrencyCode, $product->getName());
			$metaDescription = Mage::helper('tejarobservpricing')->getMetaDescription($storeCurrencyCode, $product->getName());
			
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
			
		################################## SET PRICES & SPECIAL PRICE #################################		
			
			//--- Get Product Final Cost using helper function...
			$store_rate = Mage::helper('tejarobservpricing')->getFinalCost($product, $usd_rate, $storeId, $storeCurrencyCode);
		
			//--- For Special Price...
			if($usd_specialPrice!="" || $usd_specialPrice != false){
				
				//--- Get Product Final Cost for Special Price using helper function...
				$store_special_rate = Mage::helper('tejarobservpricing')->getFinalCost($product, $usd_specialPrice,$storeId, $storeCurrencyCode);
				
			}else{
				$store_special_rate = $productDefault->getspecialPrice();
			}
	
			//--- Update Attributes...
			Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()),array('name'=>trim($productDefault->getName()),'price'=>$store_rate, 'special_price'=>$store_special_rate,'meta_title'=>$metaTitle, 'meta_description'=>$metaDescription),$storeId);	
		}//--- End foreach 
	
	}
	
	/*
	*@name        catalogProductAttributeUpdateBefore
	*@description On Mass action/Bulk Update for attributes, this Observer function set prices per store based for currently inserted product..
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $observer){
	
		//Mage::log('HELLO WORLD!', null, 'confPricing.log');
			$block = $observer->getBlock();
		if($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid){
			
			/*$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
				->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
				->load()
				->toOptionHash();*/
		
			$block->getMassactionBlock()->addItem('observpricing_updateBulkPrices', array(
				'label'=> Mage::helper('catalog')->__('Update Pricing'),
				'url'  => $block->getUrl('*/*/updateBulkPrices', array('_current'=>true)),
				/*'additional' => array(
					'visibility' => array(
						'name' => 'attribute_set',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('catalog')->__('Attribute Set'),
						'values' => $sets
					)
				)*/
			)); 			
		}
	}
	
	/*
	*@name        catalogProductMassupdatePricingBefore
	*@description On Mass action/Bulk Update for attributes, this Observer function set prices per store based 
	              for currently inserted product..
	*@parameters  Varien_Event_Observer 
	*@returns     Object
	*/
	public function catalogProductMassupdateAttributeCustom(Varien_Event_Observer $observer){
		//var_dump($observer->getEvent());die;
		foreach($observer->products as $productId){

			//--- GET Base USD Price/Rate & Special Price...
			$productDefault   = Mage::getModel('catalog/product')->setStoreId(0)->load($productId);
			$usd_rate         = $productDefault->getPrice();
			$usd_specialPrice = $productDefault->getspecialPrice();
			
			//--- Trim Product name to avoid Whitespaces issues..
			Mage::getSingleton('catalog/product_action')
				->updateAttributes(array($productDefault->getId()),array('name'=>trim($productDefault->getName())));
			
			//--- Loop through available Stores...	
			foreach(Mage::app()->getStores() as $store){
				
				$storeCurrencyCode = $store->getCurrentCurrencyCode();
				$storeId           = $store->getId();
				$product           = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
				
				//--- Get Meta Title and Meta Descriptipon for current Product...
				$metaTitle         = Mage::helper('tejarobservpricing')->getMetaTitle($storeCurrencyCode, $product->getName());
				$metaDescription   = Mage::helper('tejarobservpricing')->getMetaDescription($storeCurrencyCode, $product->getName());
				
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
				
			################################## SET PRICES & SPECIAL PRICE #################################		
					
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
				
				//--- Get Product Final Cost using helper function...
				$store_rate = Mage::helper('tejarobservpricing')->getFinalCost($product, $usd_rate, $storeId, $storeCurrencyCode);
			
				//--- For Special Price...
				if($usd_specialPrice!="" || $usd_specialPrice != false){
					
					//--- Get Product Final Cost for Special Price using helper function...
					$store_special_rate = Mage::helper('tejarobservpricing')->getFinalCost($product, $usd_specialPrice,$storeId, $storeCurrencyCode);
					
				}else{
					$store_special_rate = $usd_specialPrice;
				}
				
				//--- Update Attributes...
				Mage::getSingleton('catalog/product_action')
				->updateAttributes(array($product->getId()),array('name'=>trim($productDefault->getName()),'price'=>$store_rate, 'special_price'=>$store_special_rate,'meta_title'=>$metaTitle, 'meta_description'=>$metaDescription),$storeId);	
			}//--- End foreach 
		}
	}
	
}
