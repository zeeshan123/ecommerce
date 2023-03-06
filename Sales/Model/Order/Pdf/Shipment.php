<?php
/**
 * Sales Order Shipment PDF model
 *
 * @category   Tejar
 * @package    Tejar_Sales
 * @author     Zeeshan
 */
class Tejar_Sales_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{

   /**
     * Return PDF document
     *
     * @param  array $shipments
     * @return Zend_Pdf
     */
    public function _____________________getPdf($shipments = array())
    {
		
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
	
	
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page  = $this->newPage();
			//$this->_setFontRegular($page,12);
            $order = $shipment->getOrder();
				$this->y = 800;
			
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
			
			
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
			
			
            $this->insertOrder(
                $page,
                $shipment,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId())
            );
			
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
            );
			
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
             foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
			
	//----------------------------------------------- ZEE CODE --------------------------------------------------//
			
			$pageCustom  = $this->newPage();
			$x = 40;
			
			$this->_setFontRegular($pageCustom, 14);
			//$this->_setFontBold($pageCustom, 14);
			$pageCustom->drawText('Order Id: #'. $order->getIncrementId(), $x , $this->y+10, 'UTF-8');
			$this->y -=30;
			//$this->_setFontBold($pageCustom, 14);
			$pageCustom->drawRectangle(30, $this->y+30, $pageCustom->getWidth()-30, $this->y -200, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$pageCustom->drawText('Sent From:', $x , $this->y+10, 'UTF-8');
			$pageCustom->drawText('Sent To:', $x+240 , $this->y+10, 'UTF-8');
			
			
			//--- Zee Code: Add a dividing Line...
			$pageCustom->setLineColor(new Zend_Pdf_Color_GrayScale(0)); //black
			$pageCustom->setLineWidth(0.5);
			//echo $this->y;die;
			$pageCustom->drawLine(30, $this->y+5, 565, $this->y+5);
			
			$this->_setFontRegular($pageCustom, 12);
			//$page2->drawText('Sent TO'.$shipment->getStreetAddress(), $x+200 , $this->y+10, 'UTF-8');
			
			//$page->drawText(Mage::getStoreConfig('general/store_information/address'), $x + 150, $this->y, 'UTF-8');
			foreach (explode("\n", Mage::getStoreConfig('general/store_information/address', $shipment->getStore())) as $value){
            if ($value !== ''){
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					//echo $_value; die;
                    $pageCustom->drawText(trim(strip_tags($_value)),
                        $x+10 , $this->y-10, 
                        'UTF-8');
                    $this->y -= 18;
					}
				}
			}
			
			//---print Order Id
			
			//--- Generate Formatted User Info & Shipping Address....
			
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getName()."\n";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getEmail()."\n";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getStreetFull()." ";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getCity().", \n";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getRegion().", ";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getPostcode()."\n";
			$shippingAddressToPrint .= $shipment->getShippingAddress()->getCountryModel()->getName()."\n";
			$shippingAddressToPrint .= "T: ".$shipment->getShippingAddress()->getTelephone()."\n";
			
			//--- Add a Vertical Divider ............
			//$pageCustom->drawRectangle(250, $this->y-100, 251, $this->y +100, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$pageCustom->drawLine(250, $this->y-130, 251, $this->y +100);
			$pageCustom->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
			
			//--- Reset Y value..
			 $this->y += 18;
			$pageCustom->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			foreach (explode("\n",$shippingAddressToPrint) as $value){
            if ($value !== ''){
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					//echo $_value; die;
                    $pageCustom->drawText(trim(strip_tags($_value)),
                        $x+250 , $this->y+40, 
                        'UTF-8');
                    $this->y -= 18;
					}
				}
			}
			//$this->_setFontRegular($pageCustom, 12);
			 $pageCustom = end($pdf->pageCustom);
	//-------------------------------------------- END ZEE CODE --------------------------------------------------//
        }
			
			
		//$this->drawText(Mage::getStoreConfig('general/store_information/address'), $page, $order);
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
		//var_dump(Mage::getStoreConfig('general/store_information/address'));die;
		// $this->_drawItem(Mage::getStoreConfig('general/store_information/address'), $page, $order);die;
        return $pdf;
    }
	
	/**
     * Set font as regular (Override Abstract function)
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
	protected function __setFontRegular($object, $size = 10)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }
}