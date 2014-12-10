<?php
/**
 * Source model for configuration in System/Configuration/C4B/Product import/Preprocessing/create_attributes.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Source_Attribute_ProcessingMode
{
    const PROCESSING_MODE_INFORM_ONLY = 0;
    const PROCESSING_MODE_CREATE_AND_INFORM = 1;
    
    /**
     * Method that provides values for dropdown.
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>self::PROCESSING_MODE_CREATE_AND_INFORM, 'label'=>Mage::helper('xmlimport')->__('Create missing attributes and send an e-mail')),
            array('value'=>self::PROCESSING_MODE_INFORM_ONLY, 'label'=>Mage::helper('xmlimport')->__('Just send an e-mail with a list of missing attributes')),
        );
    }
}
