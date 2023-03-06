<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Tejar
 * @package     Tejar_CustomFilters_Block
 * @copyright   Copyright (c) 2014-2016 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 * @author      Zeeshan 
 */

class Tejar_CustomFilters_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

     /**
     * Return current URL with rewrites and additional parameters
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
       if (!$this->helper('tejar_customfilters')->isEnabled()) {
            return parent::getPagerUrl($params);
       }

        if ($this->helper('tejar_customfilters')->isCatalogSearch() ) {
            $params['isLayerAjax'] = null;
            return parent::getPagerUrl($params);
        }

        return $this->helper('tejar_customFilters')->getPagerUrl($params);
    }


}