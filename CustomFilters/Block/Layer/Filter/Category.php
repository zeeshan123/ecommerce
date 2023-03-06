<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_LayeredNavigation
 * @copyright   Copyright (c) 2014-2015 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Tejar_CustomFilters_Block_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
	 public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'tejar_customfilters/layer_filter_category';
    }
}