<?php

/**
 * Html page block
 *
 * @category   Tejar
 * @package    Tejar_Page
 * @author     Zeeshan 
 */
class Tejar_Page_Block_Html_Head extends Mage_Page_Block_Html_Head
{
	/**
	 * FOR SEO: Extending getCssJsHtml 'Core' function to manipulate fine/links ordering in <head>
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        // separate items by types
        $lines  = array();
        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }
            $if     = !empty($item['if']) ? $item['if'] : '';
            $params = !empty($item['params']) ? $item['params'] : '';
            switch ($item['type']) {
                case 'js':        // js/*.js
                case 'skin_js':   // skin/*/*.js
                case 'js_css':    // js/*.css
                case 'skin_css':  // skin/*/*.css
                    $lines[$if][$item['type']][$params][$item['name']] = $item['name'];
                    break;
                default:
                    $this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item);
                    break;
            }
        }

        // prepare HTML
        $shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
        $shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
        $html   = '';
        foreach ($lines as $if => $items) {
            if (empty($items)) {
                continue;
            }
            if (!empty($if)) {
                // open !IE conditional using raw value
                if (strpos($if, "><!-->") !== false) {
                    $html .= $if . "\n";
                } else {
                    $html .= '<!--[if '.$if.']>' . "\n";
                }
            }
			// other stuff
            if (!empty($items['other'])) {
                $html .= $this->_prepareOtherHtmlHeadElements($items['other']) . "\n";
            }
            // static and skin css
            $html .= $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s"%s />'."\n",
                empty($items['js_css']) ? array() : $items['js_css'],
                empty($items['skin_css']) ? array() : $items['skin_css'],
                $shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
            );

            // static and skin javascripts
            $html .= $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s"%s></script>' . "\n",
                empty($items['js']) ? array() : $items['js'],
                empty($items['skin_js']) ? array() : $items['skin_js'],
                $shouldMergeJs ? array(Mage::getDesign(), 'getMergedJsUrl') : null
            );

            if (!empty($if)) {
                // close !IE conditional comments correctly
                if (strpos($if, "><!-->") !== false) {
                    $html .= '<!--<![endif]-->' . "\n";
                } else {
                    $html .= '<![endif]-->' . "\n";
                }
            }
        }
        return $html;
    }
	
	/*
	*@name    getPageDefaultUrlKey
	*@desc    This function will return URL string/key excluding the domain name
	*@returns String 
	*/
	public function getPageDefaultUrlKey(){
		$urlString = Mage::helper('core/url')->getCurrentUrl();
		$url = Mage::getSingleton('core/url')->parseUrl($urlString);
		$path = $url->getPath();
		//--- Get Current Store code..
		$store            = Mage::app()->getstore();
		$currentStoreCode = $store->getCode();
		
		if($currentStoreCode == 'pk'){
			$path = ltrim($path, 'pk/');
		}elseif($currentStoreCode == 'ae'){
			$path = ltrim($path, 'ae/');
		}
		return $path;
	}
	
	/*
	*@name      getDefaultStoreUrl
	*@desc      This function will return default Store URL     
	*@return	String
	*/
	public function getDefaultStoreUrl(){
		return Mage::app()->getStore(1)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
	}
	 /**
     * Add Link element to HEAD entity
     *
     * @param string $rel forward link types
     * @param string $href URI for linked resource
     * @return Mage_Page_Block_Html_Head
     */
    public function addLinkRelThis($rel, $href)
    {
        $this->addItem('link_rel_alternate', $href, 'rel="' . $rel . '"');
        return $this;
    }
	/**
     * Classify HTML head item and queue it into "lines" array
     *
     * @see self::getCssJsHtml()
     * @param array &$lines
     * @param string $itemIf
     * @param string $itemType
     * @param string $itemParams
     * @param string $itemName
     * @param array $itemThe
     */
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe)
    {
        $params = $itemParams ? ' ' . $itemParams : '';
        $href   = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
			case 'link_rel_alternate':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
        }
    }
	/**
     * Retrieve title element text (encoded)
     *
     * @return string
     */
    public function getTitle()
    {
//echo "ZEE";die;     
	 if (empty($this->_data['title'])) {
			$isProd = Mage::registry('current_product');
			if($isProd){
				$this->_data['title'] = Mage::getStoreConfig('design/head/title_prefix') . ' ' . 'HELLO'
            . ' ' . Mage::getStoreConfig('design/head/title_suffix');
			}else{
				$this->_data['title'] = $this->getDefaultTitle();
			}
        }
		
        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }
}