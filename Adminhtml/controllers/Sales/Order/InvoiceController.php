<?php

/**
 * Adminhtml sales order edit controller
 *
 * @category   Tejar
 * @package    Tejar_Adminhtml
 * @author     Zeeshan <zeeshan.zeeshan123@gmail.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';
class Tejar_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
	/*
	* Create PDF for Shipping Label
	*/
	public function printShippingLabelAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getShippingLabelPdf(array($invoice));
                $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
	
	/*
	* Create PDF for Shipping Label
	*/
	public function printBulkShippingLabelAction()
    {
		$order_ids = $this->getRequest()->getParam('order_ids');
		$invoiceCollectionArray = array();
		foreach($order_ids as $orderId){
			$orderObject = Mage::getModel('sales/order')->load($orderId);
			$orderStatus = strtolower($orderObject->getStatusLabel());
			if($orderStatus=="processing" || $orderStatus=="complete"){
				$invoiceCollection = $orderObject->getInvoiceCollection();
				foreach($invoiceCollection as $invoice){
					array_push($invoiceCollectionArray, $invoice);
				}
			}
				//var_dump($invoice);
		}		 
		
		if (count($invoiceCollectionArray)) {
			$pdf = Mage::getModel('sales/order_pdf_invoice')->getBulkShippingLabelPdf($invoiceCollectionArray);
			$this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
				'.pdf', $pdf->render(), 'application/pdf');
			//Mage::getSingleton('core/session')->addSuccess('Invoices generated successfully!'); 
		}else{
			Mage::getSingleton('core/session')->addError('No invoices were found!'); 
			$this->_redirectReferer();
		}
		
		
		//var_dump($order->getInvoiceId());die;
		//die;

       
    }
}
