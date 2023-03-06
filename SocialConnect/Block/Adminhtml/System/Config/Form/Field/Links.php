<?php
/**
 * Tejar is not affiliated with or in any way responsible for this code.
 *
 * Commercial support is available directly from the [extension author](http://www.techytalk.info/contact/).
 *
 * @category Marko-M
 * @package SocialConnect
 * @author Marko Martinović <marko@techytalk.info>
 * @copyright Copyright (c) Marko Martinović (http://www.techytalk.info)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

abstract class Tejar_SocialConnect_Block_Adminhtml_System_Config_Form_Field_Links
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function getAuthProviderLink()
    {
        return '';
    }

    protected function getAuthProviderLinkHref()
    {
        return '';
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $this->getAuthProviderLinkHref(),
            $this->getAuthProviderLink()
        );
    }

}