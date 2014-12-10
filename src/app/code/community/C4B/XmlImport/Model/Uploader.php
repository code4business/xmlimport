<?php
/**
 * This class extends the base class to not add multiple images with the same name.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Uploader extends Mage_ImportExport_Model_Import_Uploader 
{         
    const XML_PATH_IMAGE_DUPLICATE_APPEND_SUFFIX = 'c4b_xmlimport/images/duplicate_append_suffix';
    
    /**
     * Check the configured behaviour for duplicate images. If set to no, images will not be renamed
     * and will only be moved to media directory if it is newer.
     *   
     * @return boolean
     */
    protected function _isDuplicateAppendSuffix()
    {
        return Mage::getStoreConfig(self::XML_PATH_IMAGE_DUPLICATE_APPEND_SUFFIX);
    }
    
    /**
     * Vorbid renaming files to a new name if there already exists one image with the same name.
     * @see Mage_ImportExport_Model_Import_Uploader::init()
     */
    public function init() 
    {
        parent::init();
        if( !$this->_isDuplicateAppendSuffix() )
        {
            $this->setAllowRenameFiles(false);
        }
    }
    
    /**
     * This overrides the original function and adds a check, that skips the import if the file
     * in the media/catalog/product directory is newer than the one in media/import.
     * @see Mage_ImportExport_Model_Import_Uploader::_moveFile()
     */
    protected function _moveFile($tmpPath, $destPath) 
    {
        if( !$this->_isDuplicateAppendSuffix())
        {
            return parent::_moveFile($tmpPath, $destPath);
        }
        
        $sourceFile = realpath($tmpPath);
        $destinationFile = realpath($destPath);
    
        if (file_exists($destinationFile))
        {
            $sourceStats = stat($sourceFile);
            $destinationStats = stat($destinationFile);
            if ($sourceStats['mtime'] <= $destinationStats['mtime']) 
            {
                return true;
            }
        }
    
        return ($sourceFile !== false) ? copy($sourceFile, $destPath) : false;
    }
}