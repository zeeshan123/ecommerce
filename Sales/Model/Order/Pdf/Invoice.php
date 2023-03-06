<?php
/**
 * Sales Order Shipment PDF model
 *
 * @category   Tejar
 * @package    Tejar_Sales
 * @author     Zeeshan
 */
class Tejar_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{

   /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getShippingLabelPdf($invoices = array())
    {
	//----------------------------------------------- ZEE CODE --------------------------------------------------//
		$this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
		$pageCustom  = $this->newPage();
		$x = 35;
		$invoice = $invoices[0];
	//foreach ($invoices as $invoice) {
		if($invoice->getStoreId()){
			Mage::app()->getLocale()->emulate($invoice->getStoreId());
			Mage::app()->setCurrentStore($invoice->getStoreId());
		}
		//$page  = $this->newPage();
		$order = $invoice->getOrder();
		
	
		$this->_setFontRegular($pageCustom, 14);
		$this->y = 800;
		
		//$pageCustom->drawRectangle($x, $this->y+30, 500, $this->y -10, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		//--- print Order Id
		$pageCustom->drawText('Order: #'. $order->getIncrementId(), $x+5 , $this->y, 'UTF-8');
		//$this->y -=30;
		//$this->_setFontBold($pageCustom, 14);
		//$pageCustom->drawRectangle($x, $this->y+25, 500, $this->y -250, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		
		
		//$pageCustom->drawText('To:', $x+5 , $this->y-15, 'UTF-8');
		//$pageCustom->drawLine($x, $this->y, 500, $this->y +1);
		//$pageCustom->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
		
		//--- Reset Y value..
		//$this->y -= 15;
		$pageCustom->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		
		//---Get Formatted PDF Shipping Address (Managed by admin)
		$formattedShippingAddresPDF = explode('|', $invoice->getShippingAddress()->format('pdf'));
		
		//var_dump($formattedShippingAddresPDF);die;
		//$pageCustom->drawText($formattedShippingAddresPDF,
						//$x+10 , $this->y-20, 
						//'UTF-8'); 
		
		foreach ($formattedShippingAddresPDF as $value){
			//echo $value; die;
			if ($value !== ''){
				$value = preg_replace('/<br[^>]*>/i', "\n", $value);
				foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					//echo $_value; 
					$pageCustom->drawText(trim(strip_tags($_value)),
						$x+5 , $this->y-20, 
						'UTF-8'); 
					$this->y -= 20;
				}
			}
		}
		$this->y -= 40;
		
		//$pageCustom->drawText('From:', $x+5 , $this->y+10, 'UTF-8');
		
	
		//--- Zee Code: Add a dividing Line...
		$pageCustom->setLineColor(new Zend_Pdf_Color_GrayScale(0)); //black
		$pageCustom->setLineWidth(0.5);
		//echo $this->y;die;
		//$pageCustom->drawLine($x, $this->y+5, 500, $this->y+5);
		$this->y -= 30;
		//$pageCustom->drawLine($x, $this->y+5, 500, $this->y+5);
		$this->_setFontRegular($pageCustom, 14);
	
		/*foreach (Mage::getStoreConfig('shipping/origin', $invoice->getStore()) as $value){
		if ($value !== ''){
			$value = preg_replace('/<br[^>]*>/i', "\n", $value);
			foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					$pageCustom->drawText(trim(strip_tags($_value)),$x+10 , $this->y-10, 'UTF-8');
					$this->y -= 18;
				}
			}
		}
		*/
		//--- Print Store Origin....
		$storeOrigin = Mage::getStoreConfig('shipping/origin', $invoice->getStore());
		$storeName = Mage::app()->getStore()->getName();
		$storePhone = Mage::getStoreConfig('general/store_information/phone');
		
		if($storeName!=""){
			$pageCustom->drawText(trim(strip_tags($storeName)),$x+5 , $this->y-10, 'UTF-8');
			$this->y -= 18;
		}
		if($storeOrigin['street_line1']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['street_line1'])),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['street_line2']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['street_line2'])),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		//--- Prepare city State & ZipCode String..
		$cityToPrint = "";
		if($storeOrigin['city']!=""){
			$cityToPrint .= $storeOrigin['city'].", ";
		}
		
		if($storeOrigin['region_id']!=""){
			$cityToPrint .= $storeOrigin['region_id']." ";
		}
		
		if($storeOrigin['postcode']!=""){
			$cityToPrint .= $storeOrigin['postcode'];
		}
		
		$pageCustom->drawText(trim(strip_tags($cityToPrint)),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		
		/*if($storeOrigin['city']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['city'])),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['postcode']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['postcode'])),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['region_id']!=""){
		$region = Mage::getModel('directory/region')->load($storeOrigin['region_id']);
			$state_name = $region->getName();
		if($state_name == ""){
			$state_name = $storeOrigin['region_id'];
		}
		
		$pageCustom->drawText(trim(strip_tags($state_name)),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}*/
		
		if($storeOrigin['country_id']!=""){
		$pageCustom->drawText(trim(strip_tags(Mage::app()->getLocale()->getCountryTranslation($storeOrigin['country_id']))),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		if($storePhone!=""){
		$pageCustom->drawText(trim(strip_tags($storePhone)),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		//--- Generate Formatted User Info & Shipping Address....
		/*if($invoice->getShippingAddress()->getName()!=""){
			$shippingAddressToPrint  = $invoice->getShippingAddress()->getName()."\n";
		}
		if($invoice->getShippingAddress()->getEmail()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getEmail()."\n";
		}
		if($invoice->getShippingAddress()->getStreetFull()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getStreetFull()." ";
		}
		if($invoice->getShippingAddress()->getCity()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getCity().", \n";
		}
		if($invoice->getShippingAddress()->getRegion()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getRegion().", ";
		}
		if($invoice->getShippingAddress()->getPostcode()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getPostcode()."\n";
		}
		if($invoice->getShippingAddress()->getCountryModel()->getName()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getCountryModel()->getName()."\n";
		}
		if($invoice->getShippingAddress()->getTelephone()!=""){
			$shippingAddressToPrint .= "T: ".$invoice->getShippingAddress()->getTelephone()."\n";
		}*/
		
		//var_dump($invoice->getShippingAddress()->format('pdf')); die;
		//--- Add a Vertical Divider ............
		//$pageCustom->drawRectangle(250, $this->y-100, 251, $this->y +100, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		
		$this->y -= 40;
		$this->_setFontRegular($pageCustom, 10);
		//$pageCustom->drawLine(1, $this->y+20, 700, $this->y+20);
		foreach ($invoice->getAllItems() as $item){
			if ($item->getOrderItem()->getParentItem()) {
				continue;
			}
            /* Draw item (Name)*/
			$value = preg_replace('/<br[^>]*>/i', "\n", $item->getName());
			foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
				$pageCustom->drawText(trim(strip_tags($_value)),$x+5 , $this->y-20, 'UTF-8');
				$this->y -= 10;
			}
			 /* Draw item (SKU & QTY)*/
			$pageCustom->drawText(trim(strip_tags($item->getSku())),300 , $this->y, 'UTF-8');
			$pageCustom->drawText(trim(strip_tags((int)$item->getQty())),500 , $this->y, 'UTF-8');
			
			$this->y -= 10;
        }
		 
		

		end($pdf->pageCustom);
	//-------------------------------------------- END ZEE CODE --------------------------------------------------//
        $this->_afterGetPdf();
        if ($invoice->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
	}
	
	 /**
     * Return PDF document for Orders in Bulk
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
	public function getBulkShippingLabelPdf($invoices = array())
    {
		
	//----------------------------------------------- ZEE CODE --------------------------------------------------//
		$this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
		
		//$invoice = $invoices[0];
	foreach ($invoices as $invoice) {
		$pageCustom  = $this->newPage();
		$x = 35;
		if($invoice->getStoreId()){
			Mage::app()->getLocale()->emulate($invoice->getStoreId());
			Mage::app()->setCurrentStore($invoice->getStoreId());
		}
		
		//$page  = $this->newPage();
		$order = $invoice->getOrder();
		$this->_setFontRegular($pageCustom, 14);
		$this->y = 800;
		
		//$pageCustom->drawRectangle($x, $this->y+30, 500, $this->y -10, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		//--- print Order Id
		$pageCustom->drawText('Order: #'. $order->getIncrementId(), $x+5 , $this->y, 'UTF-8');
		//$this->y -=30;
		//$this->_setFontBold($pageCustom, 14);
		//$pageCustom->drawRectangle($x, $this->y+25, 500, $this->y -250, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		
		
		//$pageCustom->drawText('To:', $x+5 , $this->y-15, 'UTF-8');
		//$pageCustom->drawLine($x, $this->y, 500, $this->y +1);
		//$pageCustom->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
		
		//--- Reset Y value..
		//$this->y -= 15;
		$pageCustom->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		
		//---Get Formatted PDF Shipping Address (Managed by admin)
		$formattedShippingAddresPDF = explode('|', $invoice->getShippingAddress()->format('pdf'));
		
		//var_dump($formattedShippingAddresPDF);die;
		//$pageCustom->drawText($formattedShippingAddresPDF,
						//$x+10 , $this->y-20, 
						//'UTF-8'); 
		
		foreach ($formattedShippingAddresPDF as $value){
			//echo $value; die;
			if ($value !== ''){
				$value = preg_replace('/<br[^>]*>/i', "\n", $value);
				foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					//echo $_value; 
					$pageCustom->drawText(trim(strip_tags($_value)),
						$x+5 , $this->y-20, 
						'UTF-8'); 
					$this->y -= 20;
				}
			}
		}
		$this->y -= 40;
		
		//$pageCustom->drawText('From:', $x+5 , $this->y+10, 'UTF-8');
		
	
		//--- Zee Code: Add a dividing Line...
		$pageCustom->setLineColor(new Zend_Pdf_Color_GrayScale(0)); //black
		$pageCustom->setLineWidth(0.5);
		//echo $this->y;die;
		//$pageCustom->drawLine($x, $this->y+5, 500, $this->y+5);
		$this->y -= 30;
		//$pageCustom->drawLine($x, $this->y+5, 500, $this->y+5);
		$this->_setFontRegular($pageCustom, 14);
	
		/*foreach (Mage::getStoreConfig('shipping/origin', $invoice->getStore()) as $value){
		if ($value !== ''){
			$value = preg_replace('/<br[^>]*>/i', "\n", $value);
			foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					$pageCustom->drawText(trim(strip_tags($_value)),$x+10 , $this->y-10, 'UTF-8');
					$this->y -= 18;
				}
			}
		}
		*/
		//--- Print Store Origin....
		$storeOrigin = Mage::getStoreConfig('shipping/origin', $invoice->getStore());
		$storeName = Mage::app()->getStore()->getName();
		$storePhone = Mage::getStoreConfig('general/store_information/phone');
		
		if($storeName!=""){
			$pageCustom->drawText(trim(strip_tags($storeName)),$x+5 , $this->y-10, 'UTF-8');
			$this->y -= 18;
		}
		if($storeOrigin['street_line1']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['street_line1'])),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['street_line2']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['street_line2'])),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		//--- Prepare city State & ZipCode String..
		$cityToPrint = "";
		if($storeOrigin['city']!=""){
			$cityToPrint .= $storeOrigin['city'].", ";
		}
		
		if($storeOrigin['region_id']!=""){
			$cityToPrint .= $storeOrigin['region_id']." ";
		}
		
		if($storeOrigin['postcode']!=""){
			$cityToPrint .= $storeOrigin['postcode'];
		}
		
		$pageCustom->drawText(trim(strip_tags($cityToPrint)),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		
		/*if($storeOrigin['city']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['city'])),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['postcode']!=""){
		$pageCustom->drawText(trim(strip_tags($storeOrigin['postcode'])),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		if($storeOrigin['region_id']!=""){
		$region = Mage::getModel('directory/region')->load($storeOrigin['region_id']);
			$state_name = $region->getName();
		if($state_name == ""){
			$state_name = $storeOrigin['region_id'];
		}
		
		$pageCustom->drawText(trim(strip_tags($state_name)),$x+10 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}*/
		
		if($storeOrigin['country_id']!=""){
		$pageCustom->drawText(trim(strip_tags(Mage::app()->getLocale()->getCountryTranslation($storeOrigin['country_id']))),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		if($storePhone!=""){
		$pageCustom->drawText(trim(strip_tags($storePhone)),$x+5 , $this->y-10, 'UTF-8');
		$this->y -= 18;
		}
		
		//--- Generate Formatted User Info & Shipping Address....
		/*if($invoice->getShippingAddress()->getName()!=""){
			$shippingAddressToPrint  = $invoice->getShippingAddress()->getName()."\n";
		}
		if($invoice->getShippingAddress()->getEmail()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getEmail()."\n";
		}
		if($invoice->getShippingAddress()->getStreetFull()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getStreetFull()." ";
		}
		if($invoice->getShippingAddress()->getCity()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getCity().", \n";
		}
		if($invoice->getShippingAddress()->getRegion()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getRegion().", ";
		}
		if($invoice->getShippingAddress()->getPostcode()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getPostcode()."\n";
		}
		if($invoice->getShippingAddress()->getCountryModel()->getName()!=""){
			$shippingAddressToPrint .= $invoice->getShippingAddress()->getCountryModel()->getName()."\n";
		}
		if($invoice->getShippingAddress()->getTelephone()!=""){
			$shippingAddressToPrint .= "T: ".$invoice->getShippingAddress()->getTelephone()."\n";
		}*/
		
		//var_dump($invoice->getShippingAddress()->format('pdf')); die;
		//--- Add a Vertical Divider ............
		//$pageCustom->drawRectangle(250, $this->y-100, 251, $this->y +100, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$this->y -= 40;
		$this->_setFontRegular($pageCustom, 10);
		//$pageCustom->drawLine(1, $this->y+20, 700, $this->y+20);
		foreach ($invoice->getAllItems() as $item){
			if ($item->getOrderItem()->getParentItem()) {
				continue;
			}
            /* Draw item (Name)*/
			$value = preg_replace('/<br[^>]*>/i', "\n", $item->getName());
			foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
				$pageCustom->drawText(trim(strip_tags($_value)), $x+5 , $this->y-20, 'UTF-8');
				$this->y -= 10;
			}
			 /* Draw item (SKU & QTY)*/
			$pageCustom->drawText(trim(strip_tags($item->getSku())),300 , $this->y-10, 'UTF-8');
			$pageCustom->drawText(trim(strip_tags((int)$item->getQty())),500 , $this->y-10, 'UTF-8');
			
			$this->y -= 10;
        }
		
		 end($pdf->pageCustom);
	//-------------------------------------------- END ZEE CODE --------------------------------------------------//
        $this->_afterGetPdf();
        if ($invoice->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
	}
        return $pdf;
	}
}