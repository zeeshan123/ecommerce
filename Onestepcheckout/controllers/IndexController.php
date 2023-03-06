<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Onestepcheckout
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Class Magestore_Onestepcheckout_IndexController
 */
require_once 'Magestore/Onestepcheckout/controllers/IndexController.php';
class Tejar_Onestepcheckout_IndexController extends Magestore_Onestepcheckout_IndexController
{

	/**
     *
     */
    public function indexAction()
    {	
	//--- Check if a user attempts to access checkout page with old url, redirect to (buy/checkout)
		if(strstr(Mage::helper('core/url')->getCurrentUrl(), 'onestepcheckout')!==false){
			return header('location: '.Mage::helper('onestepcheckout')->getCheckoutUrl());
		}
		
		//--- Check if user is ocming with additional 'register' parameter....
		$isRegister = array_key_exists('register', $this->getRequest()->getParams());
		//--- Get Admin test Value
		$allowGuestCheckout = Mage::getStoreConfig('checkout/options/guest_checkout');
		$requireCustomerToLogin = Mage::getStoreConfig('checkout/options/customer_must_be_logged');
	
		if(!Mage::getSingleton('customer/session')->isLoggedIn() && ($allowGuestCheckout==="0" && $requireCustomerToLogin==="1") || ($allowGuestCheckout==="0" && $requireCustomerToLogin==="0" && !$isRegister)){ 
			Mage::getSingleton('checkout/session')->addError($this->__('The checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
		}else{
			return parent::indexAction();
		}
    }

   /**
     *
     */
    public function saveOrderAction()
    {
        $post = $this->getRequest()->getPost();

        if (!$post){
            Mage::getSingleton('checkout/session')->addError(Mage::helper('onestepcheckout')->__('Invalid Data! Please try to do it again!'));
            return $this->_redirect('*/*/index');
        }
        $error = false;
        $helper = Mage::helper('onestepcheckout');

        $billing_data = $this->getRequest()->getPost('billing', array());
        $shipping_data = $this->getRequest()->getPost('shipping', array());

        //2014.18.11 update VAT apply start
        if (isset($billing_data['taxvat'])) {
            $billing_data['vat_id'] = trim($billing_data['taxvat']);
            $shipping_data['vat_id'] = trim($billing_data['taxvat']);
        }
        //2014.18.11 update VAT apply end

        if (isset($billing_data['onestepcheckout_comment']))
            Mage::getModel('checkout/session')->setOSCCM($billing_data['onestepcheckout_comment']);

        $delivery_security_code = $this->getRequest()->getParam('delivery_security_code', false);
        if (!empty($delivery_security_code)) {
            Mage::getModel('checkout/session')->setData('delivery_security_code', $delivery_security_code);
        }

        //JSON reponse array for wirecard payment method
        $JSONresponse = array();

        //isAjax variable is the code name of payment method
        $isAjax = $this->getRequest()->getParam('isAjax');
        //set checkout method
        $checkoutMethod = '';
        if (!$this->_isLoggedIn()) {
            $checkoutMethod = 'guest';
            if ($helper->enableRegistration() || !$helper->allowGuestCheckout()) {
                $is_create_account = $this->getRequest()->getPost('create_account_checkbox');
                $email_address = $billing_data['email'];
                if ($is_create_account || !$helper->allowGuestCheckout()) {
                    /* Changed By Adam (31/05/2016): Fix issue of expire session in onestepcheckout page */
                    if (!$email_address) {
                        Mage::getSingleton('checkout/session')->addError(Mage::helper('onestepcheckout')->__('Missing email address or session has been expired. Please enter information again.'));
                        return $this->_redirect('*/*/index');
                    }
                    /* End code */
                    if ($this->_emailIsRegistered($email_address)) {
                        $error = true;
                        Mage::getSingleton('checkout/session')->addError(Mage::helper('onestepcheckout')->__('Email is already registered.'));
                        $this->_redirect('*/*/index');
                    } else {
                        if (!$billing_data['customer_password'] || $billing_data['customer_password'] == '') {
                            $error = true;
                        } else if (!$billing_data['confirm_password'] || $billing_data['confirm_password'] == '') {
                            $error = true;
                        } else if ($billing_data['confirm_password'] !== $billing_data['customer_password']) {
                            $error = true;
                        }
                        if ($error) {
                            Mage::getSingleton('checkout/session')->addError(Mage::helper('onestepcheckout')->__('Please correct your password.'));
                            if ($isAjax) {
                                $JSONresponse['url'] = Mage::getUrl('onestepcheckout/index/index');
                            } else {
                                $this->_redirect('*/*/index');
                            }
                        } else {
                            $checkoutMethod = 'register';
                        }
                    }
                }
            }
        }
        if ($checkoutMethod != '')
            $this->getOnepage()->saveCheckoutMethod($checkoutMethod);

        //to ignore validation for disabled fields
        $this->setIgnoreValidation();

        //resave billing address to make sure there is no error if customer change something in billing section before finishing order
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        /* Start: Modified by Daniel - Improve speed */
        $result = array();
        if (isset($customerAddressId)) {
            if (Mage::getSingleton('checkout/session')->getData('different_shipping')) {
                $billing_data["use_for_shipping"] = 0;
            } else {
                $billing_data["use_for_shipping"] = 1;
            }


            $result = $this->getOnepage()->saveBilling($billing_data, $customerAddressId);
        }
        /* End: Modified by Daniel - Improve speed */
        if (isset($result['error'])) {
            $error = true;
            if (is_array($result['message']) && isset($result['message'][0]))
                Mage::getSingleton('checkout/session')->addError($result['message'][0]);
            else
                Mage::getSingleton('checkout/session')->addError($result['message']);
            if ($isAjax) {
                $JSONresponse['url'] = Mage::getUrl('onestepcheckout/index/index');
            } else {
                $this->_redirect('*/*/index');
            }
        }

        //re-save shipping address
        $shipping_address_id = $this->getRequest()->getPost('shipping_address_id', false);
        if ($helper->isShowShippingAddress()) {
            if (!isset($billing_data['use_for_shipping']) || $billing_data['use_for_shipping'] != '1') {
                /* Start: Modified by Daniel - Improve speed */
                $result = array();
                if (isset($shipping_address_id)) {
                    $result = $this->getOnepage()->saveShipping($shipping_data, $shipping_address_id);
                }
                /* End: Modified by Daniel - Improve speed */
                if (isset($result['error'])) {
                    $error = true;
                    if (is_array($result['message']) && isset($result['message'][0]))
                        Mage::getSingleton('checkout/session')->addError($result['message'][0]);
                    else
                        Mage::getSingleton('checkout/session')->addError($result['message']);
                    $this->_redirect('*/*/index');
                }
            } else {
                $result['allow_sections'] = array('shipping');
                $result['duplicateBillingInfo'] = 'true';
                // $result = $this->getOnepage()->saveShipping($billing_data, $shipping_address_id);
            }
			
			
        }
		//---------------------------------------------- ZEE CODE ----------------------------------------------//
		if ($this->_isLoggedIn()) {
			//--- Check if Selected country is not in the Config Allowed Country List
			$selectedCountry    = $this->getOnepage()->getQuote()->getshippingAddress()->getCountry();
			$allowedCountryList = $this->getOnepage()->getQuote()->getStore()->getWebsite()->getConfig('general/country/allow');
			$allowedCountryList = explode(',', $allowedCountryList);
		
			if((!is_array($allowedCountryList) && $selectedCountry !== $allowedCountryList) || (is_array($allowedCountryList) && !in_array($selectedCountry, $allowedCountryList))){
				//echo "here";die;
				$countryName = Mage::app()->getLocale()->getCountryTranslation($selectedCountry);
				$result['error']   = 1;
				$result['message'] = "Sorry we do not deliver to ".$countryName."!";
				Mage::getSingleton('checkout/session')->addError($result['message']);
				$this->_redirect('*/*/index');
				//$this->_prepareDataJSON($result);
				return;
			}
		}
		//--------------------------------------------- ZEE CODE ------------------------------------------------//
        //re-save shipping method
        $shipping_method = $this->getRequest()->getPost('shipping_method', '');
        if (($shipping_method && $shipping_method != '') && !$this->isVirtual()) {
            $result = $this->getOnepage()->saveShippingMethod($shipping_method);
            if (isset($result['error'])) {
                $error = true;
                if (is_array($result['message']) && isset($result['message'][0])) {
                    Mage::getSingleton('checkout/session')->addError($result['message'][0]);
                } else {
                    Mage::getSingleton('checkout/session')->addError($result['message']);
                }
                if ($isAjax) {
                    $JSONresponse['url'] = Mage::getUrl('onestepcheckout/index/index');
                } else {
                    $this->_redirect('*/*/index');
                }
            } else {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
            }
        }

        $paymentRedirect = false;
        //save payment method
        try {
            $result = array();
            $payment = $this->getRequest()->getPost('payment', array());
            $result = $helper->savePaymentMethod($payment);
            if ($payment) {
                $this->getOnepage()->getQuote()->getPayment()->importData($payment);
            }
            $paymentRedirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        if (isset($result['error'])) {
            $error = true;
            Mage::getSingleton('checkout/session')->addError($result['error']);
            if ($isAjax) {
                $JSONresponse['url'] = Mage::getUrl('onestepcheckout/index/index');
            } else {
                $this->_redirect('*/*/index');
            }
        }


        if ($paymentRedirect && $paymentRedirect != '') {
            return $this->_redirectUrl($paymentRedirect);
        }

        //only continue to process order if there is no error
        if (!$error) {
            try {
                $this->validateOrder();
                $result = $this->getOnepage()->saveOrder();
                //newsletter subscribe
                if ($helper->isShowNewsletter()) {
                    $news_billing = $this->getRequest()->getPost('billing');
                    if(isset($news_billing['newsletter_subscriber_checkbox'])){
                        $is_subscriber = $news_billing['newsletter_subscriber_checkbox'];
                        if ($is_subscriber) {
                            $subscribe_email = '';
                            //pull subscriber email from billing data
                            if (isset($billing_data['email']) && $billing_data['email'] != '') {
                                $subscribe_email = $billing_data['email'];
                            } else if ($this->_isLoggedIn()) {
                                $subscribe_email = Mage::helper('customer')->getCustomer()->getEmail();
                            }
                            if ($checkoutMethod == 'register') {
                                try {
                                    $customer = $this->getOnepage()->getQuote()->getCustomer();
                                    $customer->setIsSubscribed(1)
                                        ->save();
                                } catch (Exception $e) {
                                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                                    $this->_redirect('onestepcheckout');
                                    return;
                                }
                            } else {
                                //check if email is already subscribed
                                $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($subscribe_email);
                                if ($subscriberModel->getId() === NULL) {
                                    Mage::getModel('newsletter/subscriber')->subscribe($subscribe_email);
                                } else if ($subscriberModel->getData('subscriber_status') != 1) {
                                    $subscriberModel->setData('subscriber_status', 1);
                                    try {
                                        $subscriberModel->save();
                                    } catch (Exception $e) {

                                    }
                                }
                            }
                        }
                    }
                }
                $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $redirect = Mage::getUrl('onestepcheckout/index/index');
                if ($isAjax) {
                    $JSONresponse['url'] = $redirect;
                } else {
                    return $this->_redirect('*/*/index');
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $redirect = Mage::getUrl('onestepcheckout/index/index');
                if ($isAjax) {
                    $JSONresponse['url'] = $redirect;
                } else {
                    return $this->_redirect('*/*/index');
                }
            }

            $this->getOnepage()->getQuote()->save();
            Mage::dispatchEvent('controller_action_postdispatch_checkout_onepage_saveOrder', array('post' => $post, 'controller_action' => $this));

            if ($redirectUrl) {
                $redirect = $redirectUrl;
            } else {
                $redirect = Mage::getUrl('checkout/onepage/success');
            }
            if ($isAjax == 'wirecard') {
                $this->getResponse()->setBody(json_encode($JSONresponse));
            } elseif ($isAjax == 'tco') {
                //Nothing to do here
                //tco payment response the JSON code automatically
            } else {
                $this->getResponse()->setRedirect($redirect);
                return;
            }
        } else {
            $this->_redirect('*/*/index');
        }
    }
}
