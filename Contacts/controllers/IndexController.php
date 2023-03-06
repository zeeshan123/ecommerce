<?php 
/**
 * Intenso Premium Theme
 * 
 * @category    Tejar
 * @package     Tejar_Contacts
 * @author      zeeshan <zeeshan_zeeshan123@gmail.com>
 */
 
require_once 'Mage/Contacts/controllers/IndexController.php';
class Tejar_Contacts_IndexController extends Mage_Contacts_IndexController{
	
	function indexAction(){
		
		//--- Redirect to 404 if 'brand' is included within the URL: to avoid SEO issues ;
		$currURL =  Mage::helper('core/url')->getCurrentUrl(); 
		
		if(stristr($currURL, '/index')){
			$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			$this->getResponse()->setHeader('Status','404 File not found');
			$this->_forward('defaultNoRoute');
			//Mage::app()->getResponse()
			//->setRedirect(substr($currURL,0,strlen($currURL)-6), 301)
			//->sendResponse();
		}else{
			$this->loadLayout();
			$this->getLayout()->getBlock('contactForm')
				->setFormAction( Mage::getUrl('*/*/post', array('_secure' => $this->getRequest()->isSecure())) );

			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->renderLayout();
		}
	}
	
	public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                //$this->_redirect('*/*/');
				//--- Redirect to contact instead of contact/index
				$this->_redirect("contact");
                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
	
	
}

?>
