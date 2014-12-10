<?php
/**
 * Block for displaying the import error results.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Block_Importerror extends Mage_Core_Block_Template
{
    /**
     * Constructor, sets the template.
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        $this->setTemplate('c4b/xmlimport/importreport.phtml');
    }
    
    /**
     * Gets the original imported file count in the result import.
     * @return int
     */
    public function getFileCount()
    {
        $resultData = $this->getData('result_data');
        return $resultData['count_import_files'];
    }
    
    /**
     * Gets the number of imported files that have errors.
     * @return int
     */
    public function getErrorFileCount()
    {
        $resultData = $this->getData('result_data');
        return $resultData['count_error_import_files'];
    }
    
    /**
     * Returns all the file import reports that have errors.
     * @return array C4B_CommandLineImporter_Model_Results
     */
    public function getErrors()
    {
        $resultData = $this->getData('result_data');
        return $resultData['errors'];
    }
    
    /**
     * Returns the timestamp when importing was started.
     * @return string
     */
    public function getStartTime()
    {
        $resultData = $this->getData('result_data');
        return $resultData['start_time'];
    }
    
    /**
     * Returns the time taken to import the files in seconds.
     * @return int
     */
    public function getTimeTaken()
    {
        $resultData = $this->getData('result_data');
        return $resultData['time_taken'];
    }
    
    /**
     * Returns the total amount of memory used in importing
     * @return string
     */
    public function getMemoryUsed()
    {
        $resultData = $this->getData('result_data');
        return $resultData['memory_used'];
    }
}
