<?php 
/* ObservPricing Helper 
 *
 * @category   Tejar
 * @package    Tejar_ObservPricing
 * @class      Tejar_Observpricing_Helper_Data
 * @author     Zeeshan <zeeshan.zeeshan123@gmail.com>
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tejar_Observpricing_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	*@name        getRandomSKU
	*@description This function returns a random hash containing 10 alpha numeric Characters..
	*@parameters  None
	*@returns     String
	*/
	public function getRandomSKU(){
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789'; 
		$string = '';
		for($i = 0; $i <= 9; $i++) {
			$string .= strtoupper($characters[mt_rand(0, strlen($characters) - 1)]);
		}
		if($string[0]=="0"){
			return $this->getRandomSKU();
		}else{
			return $string;
		}
	}
   /*
	*@name        round_up
	*@parameters  $value, $places
	*@description to obtain EXCEL like ROUND UP functionality... 
	*@returns     Number
	*/
	public function round_up($value, $places){
		$mult = pow(10, abs($places)); 
		return $places < 0 ? ceil($value / $mult) * $mult :ceil($value * $mult) / $mult;
	}
	
	/*
	*@name        getMetaTitle
	*@parameters  $countryCode, $productTitle
	*@description to obtain Meta Title of a given product
	*@returns     String
	*/
	public function getMetaTitle($countryCode, $productTitle){
		$metaTitle = "";
		if($countryCode=="PKR"){
			$metaTitle = "Buy ".$productTitle." online in Pakistan";
		}elseif($countryCode=="AED"){
			$metaTitle = "Buy ".$productTitle." online in United Arab Emirates";
		}elseif($countryCode=="USD"){
			$metaTitle = "Buy ".$productTitle." online in United States";
		}
		return $metaTitle;
	}
	
	/*
	*@name        getMetaDescription
	*@parameters  $countryCode, $productTitle
	*@description to obtain Meta Description of a given product
	*@returns     String
	*/
	public function getMetaDescription($countryCode, $productTitle){
		$metaDescription = "";
		if($countryCode=="PKR"){
			$metaDescription = "Buy ".$productTitle." online in Pakistan along with brand warranty including 7 days return and free delivery.";
		}elseif($countryCode=="AED"){
			$metaDescription = "Buy ".$productTitle." online in United Arab Emirates along with brand warranty including 7 days return and free delivery.";
		}elseif($countryCode=="USD"){
			$metaDescription = "Buy ".$productTitle." online in United States along with brand warranty including 7 days return and free delivery.";
		}
		return $metaDescription;
	}
	
	/*
	*@name        getFinalCost
	*@parameters  $product, $usd_rate
	*@description To obtain Final cost of a product ...
	*@returns     Number float..
	*/
	public function getFinalCost($product, $usd_rate, $storeId, $storeCurrencyCode, $defaultStoreId=""){
		
		//--- Get currency rates for USD Currency
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();	
		$currencyRates = Mage::getModel('directory/currency')->getCurrencyRates('USD', array_values($allowedCurrencies));
		
		//--- Get Store Based Custom Cost and Custom Shipping Cost...
		$customCost = Mage::getStoreConfig('tejarobservpricing/store_custom_cost/custom_cost',$storeId); 
		$customShippingCost = Mage::getStoreConfig('tejarobservpricing/store_custom_cost/custom_shipping_cost',$storeId); 
	
		//--- Check if Custom Cost is available for product, if not take $customcost instead.
		$cost = $product->getData('custom_cost')!=""?$product->getData('custom_cost'):$customCost;
		//--- If Custom Cost is not set consider it 1..
		if(!$cost || $cost==null || $cost==""){
			$cost=1;
		}
		
		//--- Check if customShippingCost is available for store, if not onsider it 1 
		if(!$customShippingCost || $customShippingCost==null || $customShippingCost==""){
			$customShippingCost=0;
		}
		
		//--- If customShippingCost Cost is  not available for product, consider store value..
		if($product->getData('custom_shipping_cost')){
			$shippingCost = $product->getData('custom_shipping_cost');
		}else{
			$shippingCost = $customShippingCost;
		}
		
		//--- Get Custom Shipping Weight, take 0 if not specified...
		$productWeight = $product->getWeight()!=""?$product->getWeight():0;
		
		
		
		$customHandlingCost = Mage::getStoreConfig('tejarobservpricing/store_custom_cost/custom_handling_cost',$storeId);

		if($product->getCustomHandlingCost()){
			$handlingCost = $product->getCustomHandlingCost();
		}else{
			$handlingCost = $customHandlingCost?$customHandlingCost:0;
		}
		$store_rate = $store_rate + $handlingCost;
		
		
		//--- Generate Product Final Price including custom, dimensional, shipping cost etc..
		$finalCost = $usd_rate * $cost * $currencyRates[$storeCurrencyCode];
		
		//--- Add Shipping Cost and Weight to the finalcost..	
		$store_rate = $finalCost + ($shippingCost * $productWeight);
		
		//--- Now get the Store rate by multiplying with Store Currency Rates..
		//$store_rate = $store_rate * $currencyRates[$storeCurrencyCode];
			
		//if($storeCurrencyCode!=="USD"){
			//--- Add Flaterate Price if 
			if($store_rate > Mage::getStoreConfig('carriers/flatrate/maximum_price',$storeId)){
				$store_rate = $store_rate + Mage::getStoreConfig('carriers/flatrate/price',$storeId);
			}
			
			//--- Round up Last two digits -1 for PKR and multiply with US BASED currency rate
			if($storeCurrencyCode==="PKR"){
				$store_rate = $this->round_up($store_rate,-2)-1;	
			}elseif($storeCurrencyCode==="AED"){
				$store_rate = $this->round_up($store_rate,-1)-1;	
			}elseif($storeCurrencyCode==="USD"){
				$store_rate = $this->round_up($store_rate,+2);
				$fractionVal = $store_rate - floor($store_rate);
				if($fractionVal){
					$store_rate = floor($store_rate)+.99;
				}
			}
		//}
		
		//echo "----> ", $customHandlingCost;die;
		//--- Print the Formula..
		if($storeCurrencyCode=="USD" && 0){
			//echo $store_rate;die;
			//echo "Store Id".$defaultStoreId."<br />";
			$finalCost = $usd_rate * $cost *  $currencyRates[$storeCurrencyCode] + ($shippingCost * $productWeight) + $handlingCost;
			$toshow  = $finalCost;
			echo "(Default Price x Custom Cost x Exchange Rate) + (Product Weight x Shipping Cost) + Handling Cost<br />";
			echo "($".$usd_rate." x ".$cost." x ".$currencyRates[$storeCurrencyCode] ."+(".$productWeight." x ".$shippingCost.") + ".$handlingCost." = ".$finalCost."<br />";
			//echo "Multiply with Currency Rates => ".$store_rate." x ".$currencyRates[$storeCurrencyCode]." = ".$toshow." ".$storeCurrencyCode;
			echo "<br />";
			if($toshow > Mage::getStoreConfig('carriers/flatrate/maximum_price',$storeId)){
				echo "Add with Store Max Flat Rate Value ".$toshow ."+". Mage::getStoreConfig('carriers/flatrate/price',$storeId);	
				$toshow = $toshow + Mage::getStoreConfig('carriers/flatrate/price',$storeId);
				//$roundedup = $this->round_up($toshow,-2)-1;
			}
			
			$toshow = (float) $toshow;
			
			echo " = ".$toshow."<br />";
			if($storeCurrencyCode==="PKR"){
				$roundedup = $this->round_up($toshow,-2)-1;	
			}elseif($storeCurrencyCode==="AED"){
				$roundedup = $this->round_up($toshow,-1)-1;	
			}elseif($storeCurrencyCode==="USD"){
				$roundedup = $this->round_up($toshow,+2);
				$fractionVal = $roundedup - floor($roundedup);
				//if($fractionVal){
					$roundedup = floor($roundedup)+.99;
				//}
			}
			echo "Using Round off function ".$roundedup." ".$storeCurrencyCode."<hr />"; die;
		}
		
		return $store_rate;
	}
	
	/*
	*@name        getFinalCostQuickView
	*@parameters  $product, $usd_rate
	*@description To obtain Final cost of a product ...
	*@returns     Number float..
	*/
	public function getFinalCostQuickView($product, $usd_rate){
		
		//--- GET Default Custom Cost, Custom Shipping Cost & Dimensional Cost Values..
		$defaultCost = Mage::getResourceModel('eav/entity_attribute_collection')
		->setCodeFilter('custom_cost')
		->getFirstItem()->getDefaultValue();
			
		//--- Check if Custom Cost, Shipping Cost & Dimensional Cost was already set else take default cost..
		$cost = $product->getData('custom_cost')!=""?$product->getData('custom_cost'):$defaultCost;
		
		//--- Get weight of this product
		$weight = $product->getWeight()!=""?$product->getWeight():0;
		
		//--- Generate Product Final Price including custom, dimensional, shipping cost etc..
		$finalCost = $usd_rate * $cost;
		
		return $finalCost;
	}
	
	/*
	*@name        getCost
	*@parameters  $productBrand
	*@description To obtain brand specific cost...
	*@returns     Number float..
	*/
	public function getCost($productBrand, $storeCurrencyCode){
		
		switch(strtolower($productBrand)){
			case '4moms':
				if($storeCurrencyCode == "AED"){
					$conversionRate = 1.4;
				}elseif($storeCurrencyCode == "PKR"){
					$conversionRate = 1.25;
				}
			break;
			default:	
				$conversionRate = 1.25;
		}
		return $conversionRate;	
	}
	
}
