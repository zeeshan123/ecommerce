<?php 

class Tejar_FeaturedProducts_IndexController extends Mage_Core_Controller_Front_Action{
	
	function indexAction(){
		//echo "ZEE";die;
		//--- Redirect to 404 if 'index' is included within the URL: to avoid SEO issues ;
		$currURL =  Mage::helper('core/url')->getCurrentUrl(); 
		
		if(stristr($currURL, '/index')){
			$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			$this->getResponse()->setHeader('Status','404 File not found');
			$this->_forward('defaultNoRoute');
		}else{
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->renderLayout();
		}
	}
	
	

}

?>
