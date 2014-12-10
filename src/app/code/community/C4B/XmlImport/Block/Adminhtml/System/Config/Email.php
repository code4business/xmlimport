<?php

/**
 * Frontend model for displaying and saving emails.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Block_Adminhtml_System_Config_Email extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{   
    /**
     * Add columns to the form for Invoice Texts.
     * */
    public function __construct()
    {
        $this->addColumn('name', array(
                'label' => Mage::helper('xmlimport')->__('Name'),
                'style' => 'width:100px',
        ));
        
        $this->addColumn('email', array(
            'label' => Mage::helper('xmlimport')->__('Email'),
            'style' => 'width:200px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('xmlimport')->__('Add');
        parent::__construct();
    }
}
