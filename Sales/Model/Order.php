<?php

class Tejar_Sales_Model_Order extends Mage_Sales_Model_Order
{
   
//---------------------------------------------ZEE CODE -------------------------------------------//	
	/**
     * Cancel order
     *
     * @return Mage_Sales_Model_Order
     */
    public function cancel()
    {
        if($this->canCancel()){
            $this->getPayment()->cancel();
            $this->registerCancellation();
	
            Mage::dispatchEvent('order_cancel_after', array('order' => $this));
			
			//--- Sending Order Cancellation Email to User..
			$this->sendCancelEmail($this);
        }

        return $this;
    }
	
	
	/**
     * Send Cancel order Email
     * @name SendCancelEmail
     * @return sendmail object 
     */
	function sendCancelEmail($order){
		
		//--- Sending Order Cancellation Email to User..
		$storeId = $order->getStore()->getId();
			
		$emailTemplate = Mage::getModel('core/email_template')->loadByCode('Order Cancel - Tejar');
		$templateId    = $emailTemplate->getTemplateId();	
		//--- If no template is found return.. Do nothing.
		if(!$templateId){
			return;
		}
		$mailer = Mage::getModel('core/email_template_mailer');
        $customerName = $order->getBillingAddress()->getName();
		
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
		$mailer->addEmailInfo($emailInfo);
          // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'invoice'      => $this,
               // 'comment'      => $comment,
                'billing'      => $order->getBillingAddress()
                //'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();
	
	}
//---------------------------------------------ZEE CODE -------------------------------------------//
}
