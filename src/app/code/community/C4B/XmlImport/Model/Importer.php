<?php
/**
 * This class will collect import files then prepare the data and start the import process for each of the files. After all files are processed 
 * it will send a report of missing attributes and an error report if any were encountered. Most actions are logged to a logfile and output to stdout.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Importer
{    
    const IMPORT_FILE_EXTENSION = 'xml';
    const XML_PATH_IMPORT_DIRECTORY = 'c4b_xmlimport/general/import_xml_dir';
    const XML_PATH_IMPORT_SUCCESS_DIRECTORY = 'c4b_xmlimport/general/success_dir';
    const XML_PATH_IMPORT_ERROR_DIRECTORY = 'c4b_xmlimport/general/error_dir';
    const XML_PATH_UPLOAD_IMAGE = 'c4b_xmlimport/general/upload_image';
    
    const XML_PATH_PREPROCESSING_CREATE_CATEGORIES = 'c4b_xmlimport/preprocessing/create_category';
    
    const EVENT_AFTER_DATA_IMPORT = 'c4b_xmlimport_after_data_import';

    const RESULT_NO_VALID_FILES = 0;
    const RESULT_OK = 1;
    const RESULT_PARTIALLY_OK = 2;
    const RESULT_NO_PRODUCTS = 3;
    const RESULT_IMPORT_LOCKED = 4;

    /**
     * Collected files for importing
     * @var array(string)
     */
    protected $_importFiles = array();
    
    /**
     * Name used when obtaining a lock
     * @var string
     */
    protected $_lockName = 'c4b_xmlimport_lock';
    
    /**
     * Run the import process. Returns true on success or false on any error.
     * @param boolean $performImport
     * @return boolean
     */
    public function run()
    {
        /* @var $messageHandler C4B_XmlImport_Model_MessageHandler */
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        $messageHandler->startReport();

        $importFiles = $this->_collectImportFiles();
        $result = false;

        if( count($importFiles) == 0 )
        {
            $messageHandler->addNotice('No xml files found in import directory.');
            $result = self::RESULT_NO_PRODUCTS;
        }
        else if( count($importFiles) != 0 && Mage::getResourceHelper('xmlimport')->setNamedLock($this->_lockName) )
        {
            $result = $this->_processImportFiles($importFiles);
        }
        else
        {
            $messageHandler->addError('Could not obtain lock.');
            $messageHandler->addError('Importing will not be performed.');
            $result = self::RESULT_IMPORT_LOCKED;
        }

        $messageHandler->finalizeResults();
        Mage::getResourceHelper('xmlimport')->releaseNamedLock($this->_lockName);
        return $result;
    }
    
    /**
     * Set the name that will be used to set a DB lock.
     * @param string $lockName
     * @return C4B_XmlImport_Model_Importer
     */
    public function setLockName($lockName)
    {
        $this->_lockName = $lockName;
        return $this;
    }
    
    /**
     * Import given XML file.
     * @param string $filePath
     * @return boolean|C4B_XmlImport_Model_Importer|number
     */
    protected function _importFile($filePath)
    {
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        /* @var $products C4B_XmlImport_Model_Products */
        $products = Mage::getModel('xmlimport/products');
        
        $messageHandler->addNotice("Validating file structure.");
        $validationResult = $products->validateFile($filePath);
        
        $success = false;
        switch($validationResult)
        {
            case C4B_XmlImport_Model_Products::VALIDATION_RESULT_FILE_ERROR:
                $messageHandler->addError('File has syntax errors.');
                break;
            case C4B_XmlImport_Model_Products::VALIDATION_RESULT_NO_ROOT_NODE:
                $messageHandler->addError('File is missing the root node.');
                $success = true;
                break;
            case C4B_XmlImport_Model_Products::VALIDATION_RESULT_NO_PRODUCT_NODES:
                $messageHandler->addNotice('File has no product nodes.');
                $success = true;
                break;
            case C4B_XmlImport_Model_Products::VALIDATION_RESULT_OK:
                $messageHandler->addNotice("File structure valid.");
                
                $messageHandler->addNotice("Preparing data.");
                $importData = $products->processFile($filePath);
                $messageHandler->addNotice("Data ready.");
                
                if( !empty($importData) && count($importData) > 0 )
                {
                    $messageHandler->addNotice('Importing started.');
                    if( $this->_importData($importData) )
                    {
                        $messageHandler->addNotice('Importing completed.');
                        $success = true;
                    } else
                    {
                        $messageHandler->addErrorsForFile( basename($filePath), ' ', 'Data was not valid for import' );
                        $success = false;
                    }
                }
                else
                {
                    $messageHandler->addErrorsForFile( basename($filePath), ' ', 'File has no valid product nodes.' );
                    $success = false;
                }
                break;
        }
        return $success;
    }
    
    /**
     * Trigger the import for currently set data.
     * @param array $data
     */
    protected function _importData($data) 
    {
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        /* @var $fastSimpleImport AvS_FastSimpleImport_Model_Import */
        $fastSimpleImport = Mage::getModel('fastsimpleimport/import');
        $importResult = false;
        if (!empty($data))
        {
            try
            {
                $fastSimpleImport->processProductImport($data);
                $importResult = ( ($fastSimpleImport->getProcessedRowsCount() > 0) && ($fastSimpleImport->getProcessedRowsCount() > $fastSimpleImport->getInvalidRowsCount()) );
                Mage::dispatchEvent(self::EVENT_AFTER_DATA_IMPORT, array('imported_product_skus' => array_keys($fastSimpleImport->getEntityAdapter()->getNewSku()) ));
            }catch(Exception $e)
            {
                Mage::logException($e);
                $messageHandler->addError($e->getMessage());
            }
            
            if($fastSimpleImport->getErrorsCount() > 0)
            {
                $messageHandler->addError(Mage::helper('xmlimport')->formatErrors($fastSimpleImport->getErrorMessages()));
            }
        }
        return $importResult;
    }
    
    /**
     * Process the collected files by validating and importing them
     * @param array
     * @return int Result of file import
     */
    protected function _processImportFiles($importFiles)
    {
        /* $messageHandler C4B_XmlImport_Model_MessageHandler */
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        /* @var $helper C4B_XmlImport_Helper_Data */
        $helper = Mage::helper('xmlimport');
        $validFiles = 0;
        foreach ($importFiles as $fileName => $filePath)
        {
            $messageHandler->addNotice("Processing file {$fileName}.");
            if($this->_importFile( realpath($filePath) ))
            {
                rename( $filePath, $helper->getDirectory('success') . DS . $fileName );
                $validFiles++;
            } else
            {
                rename( $filePath, $helper->getDirectory('error') . DS . $fileName );
            }
            $messageHandler->addNotice("File {$fileName} processed.");
        }

        if($validFiles == 0)
        {
            return self::RESULT_NO_VALID_FILES;
        }
        else if($validFiles == count($importFiles))
        {
            return self::RESULT_OK;
        } else
        {
            return self::RESULT_PARTIALLY_OK;
        }
    }
    
    /**
     * Collect import files in the import directory. Returns an array of import files:
     * [FILENAME] => FILEPATH
     * @return array
     */
    protected function _collectImportFiles()
    {
        $importDir = Mage::helper('xmlimport')->getDirectory();
        /* $messageHandler C4B_XmlImport_Model_MessageHandler */
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        
        $files = scandir($importDir);
        $importFiles = array();
        foreach($files as $fileName)
        {
            $filePath = $importDir . DS . $fileName;
            $fileParts = pathinfo($filePath);
            if($fileName == '.' || $fileName == '..' || is_dir($filePath) || $fileParts['extension'] != C4B_XmlImport_Model_Importer::IMPORT_FILE_EXTENSION)
            {
                continue;
            }
            $importFiles[$fileName] = $filePath;
        }
        $messageHandler->addNotice('Number of files found in the import directory: ' . count($importFiles));
        $messageHandler->setNumberOfImportFiles( count($importFiles) );
        return $importFiles;
    }
}
