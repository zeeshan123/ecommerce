<?php
/**
 * Intenso Premium Theme
 *
 * @category    Itactica
 * @package     Itactica_Intenso
 * @copyright   Copyright (c) 2014-2015 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Tejar_Intensolocal_Helper_Data extends Itactica_Intenso_Helper_Data
{
	
	/**
     * folder path for CSS files generated by config
     */
	// session_start();
	
	//self::getCurrentTheme;
    const CSS_CONFIG_FOLDER		= 'css/config/';
	/* 3SD CODE For Styling Admin Panel*/
    const CSS_CONFIG_PATH		= 'frontend/intenso/tejar/';
   
    const CSS_TEMPLATE_PATH		= 'itactica_intenso/css/';
	

	/**
     * file path of CSS settings file
     * @access public
     * @return string
     */
	public function getConfigCssPath() {
		
		return Mage::getBaseDir('skin') . '/' . self::CSS_CONFIG_PATH;
		//return Mage::getBaseDir('skin') . '/' . self::CSS_CONFIG_PATH.$_SESSION['curr_theme']."/";
		//return Mage::getBaseDir('skin') . '/frontend/intenso/' . Mage::getDesign()->getTheme('frontend').'/';
	}

	

    /**
     * return true if secondary menu is active
     * @access public
     * @return boolean
     */
    public function showSecondaryMenu() {
    	return Mage::getStoreConfig('intenso/header/sec_menu', Mage::app()->getStore());
    }

    /**
     * return true if Company field must be enabled in checkout
     * @access public
     * @return bool
     */
    public function useCompanyField() {
		return Mage::getStoreConfig('intenso/checkout/company_field', 
			Mage::app()->getStore());
	}

	/* 3SD CODE For Enabled , Disabled Mobile Menu*/
	public function mobileStickyMenu($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso_design/header/mobile_sticky', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled Mobile Menu*/
	public function tabletStickyMenu($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso_design/header/tablet_sticky', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled homeCategoryDropDown Menu*/
	public function homeCategoryDropDown($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso_design/header/home_category_dropdown', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled Cart SideBar*/
	public function ShowCartSideBar($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso/checkout/custom_cart_sidebar', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled Fax Field*/
	public function faxField($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso/sales/fax_field', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled Company Field*/
	public function companyField($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso/sales/company_field', $storeId);
    }
	/* 3SD CODE For Enabled , Disabled Company Field*/
	public function zipCodeField($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso/sales/zip_code_field', $storeId);
    }
	
	/* 3SD CODE Zip code Optional Hide */
	public function displayOptionalPostalCode($storeId = NULL) {
        if ($storeId === NULL) {
            $storeId = Mage::app()->getStore();
        }
        return Mage::getStoreConfig('intenso/sales/display_optional_postal_code', $storeId);
    }
	
	/* 3SD CODE For URL XML */
	 public function getAddressBookUrl()
    {
        return $this->_getUrl('customer/address');
    }
	 public function getBillingAgreement()
    {
        return $this->_getUrl('sales/billing_agreement');
    }
	public function getMyProductReviews()
    {
        return $this->_getUrl('review/customer');
    }
	public function getRecurringProfiles()
    {
        return $this->_getUrl('sales/recurring_profile');
    }
	public function getMyTags()
    {
        return $this->_getUrl('tag/customer');
    }	
	public function getMyApplications()
    {
        return $this->_getUrl('oauth/customer_token');
    }
	public function getNewsletterSubscriptions()
    {
        return $this->_getUrl('newsletter/manage');
    }
	public function getMySavedCards()
    {
        return $this->_getUrl('customer/savedcards');
    }
	public function getMyDownloadableProducts()
    {
        return $this->_getUrl('downloadable/customer/products');
    }
	/* 3SD CODE For URL XML */
	
	/**
	 * returns the URL for the alternative image, if any
	 * @access public
	 * @param Mage_Catalog_Model_Product $product
	 * @param int $width
	 * @param int $height
	 * @param bool $isLazyLoading
	 * @return bool
	 */
	public function getAlternativeImgUrl($product, $width, $height, $isLazyLoading = false) {
		$store = Mage::app()->getStore();
		if (!Mage::getStoreConfig('intenso/catalog/alternative_image', $store)) {
			return false;
		}
		$column = Mage::getStoreConfig('intenso/catalog/alt_img_column', $store);
		$value = Mage::getStoreConfig('intenso/catalog/alt_img_value', $store);
		if ($altImg = $product->load('media_gallery')->getMediaGalleryImages()->getItemByColumnValue($column, $value)) {
			if ($imgFile = $altImg->getFile()) {
				if ($isLazyLoading) {
					return '<img class="alt-image" src="'.Mage::getDesign()->getSkinUrl('images/clear.png').'" data-echo="'.Mage::helper('catalog/image')->init($product, 'small_image', $imgFile)->resize($width, $height).'" width="'.$width.'" height="'.$height.'" alt="'.$product->getName().'">';
				} else {
					return '<img class="alt-image" src="'.Mage::helper('catalog/image')->init($product, 'small_image', $imgFile)->resize($width, $height).'" width="'.$width.'" height="'.$height.'" alt="'.htmlspecialchars($product->getName()).'">';
				}
			}
		}
	}
    

}