<?php
class Tejar_CustomFilters_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
     * Delimiter for multiple filters
     */
    const MULTIPLE_FILTERS_DELIMITER = ',';

   /**
     * Check if we are in the catalog search
     * @return boolean
     */
    public function isCatalogSearch()
    {
        $pathInfo = $this->_getRequest()->getPathInfo();
		//echo $pathInfo;
        if (stripos($pathInfo, '/newproducts') !== false ||
            stripos($pathInfo, '/deals') !== false ||
            stripos($pathInfo, '/best-seller') !== false ||
            stripos($pathInfo, '/new-arrival') !== false ||
            stripos($pathInfo, '/most-viewed') !== false ||
            stripos($pathInfo, '/featured') !== false
		) {
            return true;
        }
        return false;
    }
	 /**
     * Retrieve routing suffix
     * @return string
     */
    public function getRoutingSuffix()
    {
        return 'newproducts' . Mage::getStoreConfig('tejar_customfilters/catalog/routing_suffix');
    }
   /**
     * Getter for layered navigation params
     * If $params are provided then it overrides the ones from registry
     * @param array $params
     * @return array|null
     */
    public function getCurrentLayerParams(array $params = null)
    {
        $layerParams = Mage::registry('layer_params');

        if (!is_array($layerParams)) {
            $layerParams = array();
        }

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if ($value === null) {
                    unset($layerParams[$key]);
                } else {
                    $layerParams[$key] = $value;
                }
            }
        }

        // Sort by key - small SEO improvement
        ksort($layerParams);
        return $layerParams;
    }

    /**
     * Method to get url for layered navigation
     * @param array $filters      array with new filter values
     * @param boolean $noFilters  to add filters to the url or not
     * @param array $q            array with values to add to query string
     * @return string
     */
    public function getFilterUrl(array $filters, $noFilters = false, array $q = array())
    {
        // get current url to extract query string parameters
        $query = array(
            'isLayerAjax' => null, // this needs to be removed because of ajax request
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $query = array_merge($query, $q);
        $params = array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $query,
            '_escape' => false,
        );
        $urlParts = Mage::getUrl('*/*/*', $params);

        // get filters and paths
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $layerParams = $this->getCurrentLayerParams($filters);
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
       /* $currentCategory = Mage::registry('current_category');
		
        $currentPath = str_replace($suffix, '', $currentCategory->getUrlPath());
        if (strpos($currentUrl, '/cat/') !== false) {
            $currentCategoryParts = explode('/', $currentPath);
            $normalizedPath = str_replace('/' . end($currentCategoryParts), '', $currentPath); // remove last cat
        } else {
            $currentCategoryParts = 0;
            $normalizedPath = $currentPath;
        }*/
        $urlPath = '';

        if (!$noFilters) {
            // add filters
            foreach ($layerParams as $key => $value) {
                if ($key == 'cat') {
                    // decode slash and remove url suffix
                    $value = str_replace(urlencode('/'), '/', urlencode($value));
                    $value = str_replace($suffix, '', $value);
                    // remove parent category and take only last category path
                    $catPath = explode('/',$value);
                    $value = end($catPath);
                    $urlPath .= "/{$key}/{$value}";
                    $url = (count($currentCategoryParts) > 1 && empty($filters)) ? $normalizedPath : $currentPath;
                } else {
                    // encode and replace escaped delimiter with the delimiter itself
                    $value = str_replace(urlencode(self::MULTIPLE_FILTERS_DELIMITER), self::MULTIPLE_FILTERS_DELIMITER, urlencode($value));
                    $urlPath .= "/{$key}/{$value}";
                    // remove last category from url
                    $url = (count($currentCategoryParts) > 1 && !isset($filters['cat'])) ? $normalizedPath : $currentPath;
                }
                $url = Mage::getBaseUrl() . $url . $this->getRoutingSuffix() . $urlPath;
            }
        }

        // Skip adding routing suffix for links with no filters
        if (empty($urlPath) && empty($urlParts[1])) {
            if ($resetFilterUrl = Mage::getSingleton('catalog/session')->getResetFiltersUrl()) {
                return $resetFilterUrl;
            } else {
                return Mage::getBaseUrl() . $currentPath . $suffix;
            }
        } elseif (empty($urlPath) && !empty($urlParts[1])) {
            return $urlParts;
        }

        $urlParts = explode('?', $urlParts);
        $url = $url . $suffix;
        if (!empty($urlParts[1])) {
            $url .= '?' . $urlParts[1];
        }
        return $url;
    }

    /**
     * Get the url to clear all layered navigation filters
     * @return string
     */
    public function getClearFiltersUrl()
    {
        return $this->getFilterUrl(array(), true);
    }

    /**
     * Get url for layered navigation pagination
     * @param array $query
     * @return string
     */
    public function getPagerUrl(array $query)
    {
        return $this->getFilterUrl(array(), false, $query);
    }
  /**
     * Check if module is enabled or not
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('itactica_layerednavigation/catalog/enabled');
    }

    /**
     * Check if ajax is enabled
     * @return boolean
     */
    public function isAjaxEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('itactica_layerednavigation/catalog/ajax_enabled');
    }
 /**
     * Check if price slider is enabled
     * @return boolean
     */
    public function isPriceSliderEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('itactica_layerednavigation/catalog/price_slider');
    }
    /**
     * Check if multipe choice filters is enabled
     * @return boolean
     */
    public function isMultipleChoiceFiltersEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('itactica_layerednavigation/catalog/multiple_choice_filters');
    }
}