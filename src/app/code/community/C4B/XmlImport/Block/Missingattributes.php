<?php
/**
 * Block for displaying the missing attributes during import.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Block_Missingattributes extends Mage_Core_Block_Template
{
    /**
     * Constructor, sets the template.
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        $this->setTemplate('c4b/xmlimport/missing_attributes.phtml');
    }
    
    /**
     * Get the array of created attributes
     * @return array
     */
    public function getAttributesList()
    {
        return $this->getData('attributes');
    }
    
    /**
     * If the notice about assigning created attributes to attribute sets should be displayed
     * @return boolean
     */
    public function getAreAttributesCreated()
    {
        return $this->getData('attributes_created');
    }
}
