<?php
class Tejar_Email_Model_Observer
{
    public function sendReviewEmails()
    {
		//$code = 'tejar_delayed_emails_code';

       // if (Mage::app()->loadCache($code)) {
           // return;
       // }
		//Mage::app()->saveCache(1, $code, array(), 10);
		//--ZEE CODE
		//===================================== Logic to go into OBSERVER =====================================//
		$orders  = Mage::getSingleton('sales/order')->getCollection()
			->addFieldToFilter('status', array('complete'))
			//->addFieldToFilter('customer_email', array('neq' => 'NULL' ))
			->addFieldToFilter('customer_email', array('neq' => null))->load();
			
		foreach($orders as $order){
			$orderReviewDaysLimit = Mage::getStoreConfig('catalog/order/days_limit_review', $order->getStore()->getId());
			$lastOrderTime = time() - strtotime($order->getCreatedAt());
			$lastOrderTime = floor($lastOrderTime/(60 * 60 * 24));
			//Mage::log('Sent Status - '.$order->getSentRatingEmail().'--- Rating -- '.$order->getCustomerEmail(), null, 'zeeshan.log');
			if($order->getStatus()=="complete" && $lastOrderTime>=$orderReviewDaysLimit && $order->getSentRatingEmail()==0){
			//Mage::log('Sent Status - 0'.$order->getSentRatingEmail().'--- Rating -- '.$order->getCustomerEmail(), null, 'zeeshan.log');
				$storeId          = $order->getStore()->getId();
				$mailer           = Mage::getModel('core/email_template_mailer');
				$emailInfo        = Mage::getModel('core/email_info');
				
				// Retrieve specified view block from appropriate design package (depends on emulated store)
				$paymentBlock     = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
				$paymentBlockHtml = $paymentBlock->toHtml();
				
               // echo $order->getCustomerEmail();die;
				
				$emailInfo->addTo((string)$order->getCustomerEmail(), (string)$order->getCustomerName());
                $mailer->addEmailInfo($emailInfo);
				
				//--- Set Email Template Id  if Shipment Method: Tejar Pickup Parcel was used...
				$customEmailTemplate = Mage::getModel('core/email_template')->loadByCode('Tejar - Rating Review Email');
				$templateId          = $customEmailTemplate->getTemplateId();
				
				//--- Set sender info
				$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
				$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
				$sender = array('name' => (string)$senderName,'email' => (string)$senderEmail);
				
				//--- Set all required params and send emails
				$mailer->setSender($sender, $storeId);
				$mailer->setStoreId($storeId);
				$mailer->setTemplateId($templateId);
				$mailer->setTemplateParams(array(
					'order'        => $order,
					'shipment'     => $order->getShippingAddress(),
					'comment'      => '',
					'billing'      => $order->getBillingAddress(),
					'payment_html' => $paymentBlockHtml
					)
				);
				try{
				$mailer->send();
				 }catch(Exception $exception) {
					 Mage::log($e, null, 'zeeshan.log');
					// echo "zee";die;
					// Stop store emulation process
					// $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
					throw $exception;
				}
				//--- Set Sent Rating to 1
				$order->setSentRatingEmail(1)->save();	
			}
		}
		
		//Mage::app()->removeCache($code);

        //return $this;
		//===================================== Logic to go into OBSERVER =====================================//
    }
	
	
	/*
	*
	*
	*
	*/
	public function getMaxOrderTime($order){
			$orderDelayDaysLimit = Mage::getStoreConfig('catalog/order/days_limit', $order->getStore()->getId());
			$storeWeekEnds       = explode(',', Mage::getStoreConfig('general/locale/weekend', $order->getStore()));
			$storeWeekEndsText   = array();
			$returnArray         = array();
			
			$daysOfWeek    = array();
			$daysOfWeek[0] = "Sun"; 
			$daysOfWeek[1] = "Mon"; 
			$daysOfWeek[2] = "Tue"; 
			$daysOfWeek[3] = "Wed"; 
			$daysOfWeek[4] = "Thu"; 
			$daysOfWeek[5] = "Fri"; 
			$daysOfWeek[6] = "Sat"; 
			//echo "Store Name: <strong>". Mage::getStoreConfig('general/store_information/name', $order->getStore())."</strong><br />";
			
			
			foreach($storeWeekEnds as $sw){
				
				array_push($storeWeekEndsText, $daysOfWeek[$sw]);
			}
			$milliSeconds = 0 ;
			//--- initialize day count
			$j = (int) $orderDelayDaysLimit;
			$i = 0;
			while($j>=1){
				//echo "ddd";die;
				$day = date('D',strtotime($order->getCreatedAt())+$milliSeconds);
				if(!in_array($day, $storeWeekEndsText)){
					
					$returnArray[$i] = strtotime(date('Y-M-d D',strtotime($order->getCreatedAt())+$milliSeconds));
					
				
					$j--;
				}
				$milliSeconds += 3600*24;
				$i++;
			}
			
			return $returnArray;
			
	}
	
	public function sendDelayEmails()
    {
		$orders  = Mage::getSingleton('sales/order')->getCollection()
			->addFieldToFilter('status', array( 'processing'))
			//->addFieldToFilter('customer_email', array('neq' => 'NULL' ))
			->addFieldToFilter('customer_email', array('neq'=>null))->load();
			
		$i=0;
	/*	foreach($orders as $order){
			$currentTime = strtotime(date('Y-M-d'));
			$maxOrderTime = $this->getMaxOrderTime($order);
			//echo "<pre>";var_dump( $maxOrderTime);die;
			var_dump( $currentTime >= max($maxOrderTime));
			echo $order->getCreatedAt()." -- ".$order->getSentDelayEmail()." --- ".date('Y-m-d',max($maxOrderTime))."<br />";
		}
		die;
		*/
		foreach($orders as $order){
		
			$currentTime = strtotime(date('Y-M-d'));
			$storeId       = $order->getStore()->getId();
			//--- Get Max order limit, exclude weekends on store config...
			$maxOrderTime = $this->getMaxOrderTime($order);
			
			if($order->getStatus()=="processing" && $currentTime >= max($maxOrderTime) && $order->getSentDelayEmail()==0){
				Mage::log('HELLO FROM CRON --- Delayed '.$order->getSentDelayEmail()." -- ".$order->getCustomerEmail(), null, 'zeeshan.log');
				
				// Retrieve specified view block from appropriate design package (depends on emulated store)
				$paymentBlock     = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
				$paymentBlockHtml = $paymentBlock->toHtml();
				
                $mailer    = Mage::getModel('core/email_template_mailer');
				$emailInfo = Mage::getModel('core/email_info');
				
				$emailInfo->addTo((string)$order->getCustomerEmail(), (string)$order->getCustomerName());
                $mailer->addEmailInfo($emailInfo);
				
				//--- Set Email Template Id  if Shipment Method: Tejar Pickup Parcel was used...
				$customEmailTemplate = Mage::getModel('core/email_template')->loadByCode('Tejar - Order Delayed Email');
				$templateId          = $customEmailTemplate->getTemplateId();
				//echo $templateId;die;
				//--- Set sender info
				$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
				$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
				$sender = array('name' => (string)$senderName,'email' => (string)$senderEmail);
				
				//--- Set all required params and send emails
				$mailer->setSender($sender, $storeId);
				$mailer->setStoreId($storeId);
				$mailer->setTemplateId($templateId);
				$mailer->setTemplateParams(array(
						'order'        => $order,
						'shipment'     => $order->getShippingAddress(),
						'comment'      => '',
						'billing'      => $order->getBillingAddress(),
						'payment_html' => $paymentBlockHtml
					)
				);
				try{
					$mailer->send();
				 }catch(Exception $exception) {
					 Mage::log($e, null, 'zeeshan.log');
					// echo "zee";die;
					// Stop store emulation process
					// $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
					throw $exception;
				}
				//--- Set Sent Order Delay to 1
				$order->setSentDelayEmail(1)->save();
				//$count2++;
			}
			$i++;
		}
			
			
		
		
		//Mage::app()->removeCache($code);
        //return $this;
    }
	
	public function testCron(){
		// send email
		$mail = Mage::getModel('core/email')
		 ->setToEmail('zeeshan.innovative@gmail.com')
		 ->setBody('NEW--Testing cron by executing it every minute.')
		 ->setSubject('Subject: Testing Cron Task (every 1 minutes) '.date("Y-m-d H:i:s"))
		 ->setFromEmail('info@tejar.com')
		 ->setFromName('Tejar')
		 ->setType('html');
		 
		$mail->send();
	}
	
	public function testCronSingle(){
		// send email
		Mage::log('ssss', null, 'zeeshan.log');
		$code = 'tejar_delayed_emails_code';

        if (Mage::app()->loadCache($code)) {
            return;
        }

        Mage::app()->saveCache(1, $code, array(), 10);

        $orders  = Mage::getSingleton('sales/order')->getCollection()->addFieldToFilter('status', array('complete', 'processing'))
            ->load();
		//echo count($orders);
		Mage::log('HELLO FROM CRON ---> Order Status: '.count($orders), null, 'zeeshan.log');
		/*try{
			foreach($orders as $order){
				Mage::log('HELLO FROM CRON ---> Order Status: '.$order->getStatus(), null, 'zeeshan.log');
			}
		}catch (Mage_Core_Exception $e) {
			Mage::log($e, null, 'zeeshan.log');
		}*/

        Mage::app()->removeCache($code);

        return $this;
		
	}
}