<?php
class Tejar_CustomFilters_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
     const NEW_PRODUCTS_URL_KEY = 'newproducts/';
    public function initControllerRouters($observer)
    {
		
         $front = $observer->getEvent()->getFront();
        // Collect routes - needed for match()
        $this->collectRoutes('frontend', 'standard');
        $front->addRouter('tejar_customfilters', $this);
    }

  /*  public function match(Zend_Controller_Request_Http $request)
    {

        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        $urlKey = trim($request->getPathInfo(), '/');
        if ($urlKey == self::NEW_PRODUCTS_URL_KEY) {
            $request->setModuleName('newproducts/index')
                ->setControllerName('index')
                ->setActionName('index');
            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                $urlKey
            );
            return true;
        }
        return false;
    }
	*/
	/**
     * Match the request
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
       $helper = Mage::helper('tejar_customfilters');
        if (!$helper->isEnabled()) {
            return false;
        }

        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        $identifier = ltrim($request->getPathInfo(), '/');
        $identifier = substr($identifier, 0, strlen($identifier) - strlen($suffix));
        $urlSplit = explode($helper->getRoutingSuffix(), $identifier, 2);

        // Check if it is a link generated by the SEO module
        if (!isset($urlSplit[1])) {
            return false;
        }

        $urlRewrite = Mage::getModel('core/url_rewrite');
        $urlRewrite->setStoreId(Mage::app()->getStore()->getId());

        // Parse url params
        $params = explode('/', trim($urlSplit[1], '/'));
        $layerParams = array();
        $total = count($params);
        for ($i = 0; $i < $total - 1; $i++) {
            if (isset($params[$i + 1])) {
                $layerParams[$params[$i]] = urldecode($params[$i + 1]);
                ++$i;
            }
        }

        if (isset($layerParams['cat'])) {
            $catPath = $urlSplit[0] . '/' . $layerParams['cat'] . $suffix;
            $layerParams['cat'] = $urlSplit[0] . '/' . $layerParams['cat'] . $suffix;
        } else {
            $catPath = $urlSplit[0] .$suffix;
        }
        $urlRewrite->loadByRequestPath($catPath);

        // Check if a valid category is found
        if ($urlRewrite->getId()) {
            $modules = $this->getModuleByFrontName('catalog');

            $found = false;

            // Find the controller to be executed
            // It takes into account rewrites
            foreach ($modules as $realModule) {
                $request->setRouteName($this->getRouteByFrontName('catalog'));

                // Check if this place should be secure
                $this->_checkShouldBeSecure($request, '/catalog/category/view');

                // Find controller class name
                $controllerClassName = $this->_validateControllerClassName($realModule, 'category');
                if (!$controllerClassName) {
                    continue;
                }

                // Instantiate controller class
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());

                // Check if controller has viewAction() method
                if (!$controllerInstance->hasAction('view')) {
                    continue;
                }
                $found = true;
                break;
            }

            // Check if we found a controller
            if (!$found) {
                return false;
            }

            // Set the required data on $request object
            $request->setPathInfo($urlRewrite->getTargetPath());
            $request->setRequestUri('/' . $urlRewrite->getTargetPath());
            $request->setModuleName('catalog')
                ->setControllerName('category')
                ->setActionName('view')
                ->setControllerModule($realModule)
                ->setParam('id', $urlRewrite->getCategoryId())
                ->setAlias(
                    Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $catPath
            );

            // Add post params to parsed ones from url
            // Usefull to easily override params
            $layerParams += $request->getPost();
            // Add params to request
            $request->setParams($layerParams);

            // Save params in registry - used later to generate links
            Mage::register('layer_params', $layerParams);

            // dispatch action
            $request->setDispatched(true);
            $controllerInstance->dispatch('view');

            return true;
        }
        return false;
    }
}