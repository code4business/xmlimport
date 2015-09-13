<?php
/**
 * Block for displaying the import report.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 *
 * @method Varien_Object getResults()
 */
class C4B_XmlImport_Block_Importer_Report extends Mage_Core_Block_Template
{
    /**
     * Get Starting timestamp
     *
     * @return string
     */
    public function getStartingTime()
    {
        return $this->getResults()->getStartTime();
    }

    /**
     * Get the overall duration of import.
     *
     * @return float
     */
    public function getTimeTaken()
    {
        return round($this->getResults()->getTimeTaken(), 3);
    }

    /**
     * Get overall duration in minutes.
     *
     * @return float
     */
    public function getTimeTakenInMinutes()
    {
        return round(($this->getTimeTaken() / 60) ,2);
    }

    /**
     * Get total number of import files.
     *
     * @return int
     */
    public function getImportFileCount()
    {
        return $this->getResults()->getCountImportFiles();
    }

    /**
     * Get the number of import files with errors.
     *
     * @return int
     */
    public function getImportFileWithErrorCount()
    {
        return $this->getResults()->getCountErrorImportFiles();
    }

    /**
     * If any import errors were logged.
     *
     * @return bool
     */
    public function hasImportErrors()
    {
        return count($this->getErrors()) != 0;
    }

    /**
     * Get list of reported missing attributes.
     *
     * @return array
     */
    public function getMissingAttributes()
    {
        return $this->getResults()->getMissingAttributes();
    }

    /**
     * If any missing attributes were reported.
     *
     * @return bool
     */
    public function hasMissingAttributes()
    {
        return count($this->getMissingAttributes()) != 0;
    }

    /**
     * Get the reported error messages.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getResults()->getErrors();
    }
}