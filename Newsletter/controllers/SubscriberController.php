<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter subscribe controller
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Newsletter/controllers/SubscriberController.php';
class Tejar_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
	public function indexAction()
    {
        $this->loadLayout();
         $this->_initLayoutMessages('customer/session');
         $this->_initLayoutMessages('catalog/session');

         if ($block = $this->getLayout()->getBlock('customer_unsubscribe')) {
             $block->setRefererUrl($this->_getRefererUrl());
         }
        $this->getLayout()->getBlock('head')->setTitle($this->__('Newsletter Unsubscribe'));
        $this->renderLayout();
    }
	
    /**
     * Unsubscribe newsletter
     */
    public function unsubscribeAction()
    {
		
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');
	//echo "shariq"; die;
        if ($id && $code) {
            $session = Mage::getSingleton('core/session');
            try {
                Mage::getModel('newsletter/subscriber')->load($id)
                    ->setCheckCode($code)
                    ->unsubscribe();
               // $session->addSuccess($this->__('You have been unsubscribed.'));
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $e->getMessage());
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the un-subscription.'));
            }
        }
        //$this->_redirectReferer();
		$this->_redirect('newsletter/subscriber/');
		
		
    }
}
