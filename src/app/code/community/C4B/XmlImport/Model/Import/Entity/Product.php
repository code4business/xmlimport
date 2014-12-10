<?php

/**
 * Change the way the file uploader object is instantiated, so that the factory is used instead of new, making it possible to rewrite it.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Import_Entity_Product extends AvS_FastSimpleImport_Model_Import_Entity_Product
{
    /**
     * Use factory to get fileUplaoder object.
     * @see AvS_FastSimpleImport_Model_Import_Entity_Product::_getUploader()
     */
    protected function _getUploader()
    {
        if (is_null($this->_fileUploader))
        {
            $this->_fileUploader    = Mage::getModel('importexport/import_uploader', null); // Use factory method instead of new
            $this->_fileUploader->init();
    
            $tmpDir     = Mage::getConfig()->getOptions()->getMediaDir() . '/import';
            $destDir    = Mage::getConfig()->getOptions()->getMediaDir() . '/catalog/product';
            if (!is_writable($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            // diglin - add auto creation in case folder doesn't exist
            if (!file_exists($tmpDir)) {
                @mkdir($tmpDir, 0777, true);
            }
            if (!$this->_fileUploader->setTmpDir($tmpDir)) {
                Mage::throwException("File directory '{$tmpDir}' is not readable.");
            }
            if (!$this->_fileUploader->setDestDir($destDir)) {
                Mage::throwException("File directory '{$destDir}' is not writable.");
            }
        }
        return $this->_fileUploader;
    }
}
