<?php
/**
 * Class responsible for saving, logging and displaying information about import process. It also sends an email with collected errors
 * to recipients configured in the store.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 * 
 * @method int getStartMicroTime()
 * @method C4B_XmlImport_Model_MessageHandler setStartMicroTime(int)
 * @method int getStartMem()
 * @method C4B_XmlImport_Model_MessageHandler setStartMem(int)
 * @method string getStartTimestamp()
 * @method C4B_XmlImport_Model_MessageHandler setStartTimestamp(string)
 * @method string getTimeTaken()
 * @method C4B_XmlImport_Model_MessageHandler setTimeTaken(string)
 * @method int getMemoryUsed()
 * @method C4B_XmlImport_Model_MessageHandler setMemoryUsed(int)
 * @method int getNumberOfImportFiles()
 * @method C4B_XmlImport_Model_MessageHandler setNumberOfImportFiles(int)
 */
class C4B_XmlImport_Model_MessageHandler extends Varien_Object
{
    const DEFAULT_LOG_FILE_NAME = 'import.log';
    
    const XML_PATH_NOTIFICATIONS_MISSING_ATTRIBUTES_RECIPIENTS = 'c4b_xmlimport/notifications/missing_attributes_recipients';
    const XML_PATH_NOTIFICATIONS_MISSING_ATTRIBUTES_EMAIL_TEMPLATE = 'c4b_xmlimport/notifications/missing_attributes_template';
    const XML_PATH_NOTIFICATIONS_IMPORT_ERROR_RECIPIENTS = 'c4b_xmlimport/notifications/import_error_receipients';
    const XML_PATH_NOTIFICATIONS_IMPORT_ERROR_EMAIL_TEMPLATE = 'c4b_xmlimport/notifications/import_error_template';

    /**
     * @var DateTime
     */
    protected $_dateTime = null;

    /**
     * 
     * @var array
     */
    protected $_errors = array();
    
    /**
     * XML error types
     * @var array
     */
    protected $_xmlErrorMessage = array('none','warning','error','fatal');

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_dateTime = new DateTime('now', new DateTimeZone( Mage::getStoreConfig('general/locale/timezone')));
    }

    /**
     * Log message as notice.
     * @param string $message
     * @param boolean $print
     */
    public function addNotice($message)
    {
        return $this->_addMessage($message, Zend_Log::NOTICE);
    }
    
    /**
     * Log message as error.
     * @param string $message
     * @param string $print
     */
    public function addError($message)
    {
        if(is_array($message))
        {
            foreach ($message as $singleMessage)
            {
                $this->addError($singleMessage);
            }
        } else
        {
        $this->_addMessage($message, Zend_Log::ERR);
        }
    }

    /**
     * Save, print and output error messages. Messages are saved per file and product.
     * @param string $filename
     * @param string $productNumber
     * @param array|string $error
     */
    public function addErrorsForFile($filename, $productNumber, $error)
    {
        if(is_array($error))
        {
            foreach($error as $singleError)
            {
                $this->addErrorsForFile($filename, $productNumber, $singleError);
            }
        } else
        {
            if( !array_key_exists($filename, $this->_errors) )
            {
                $this->_errors[$filename] = array();
            }
            if( array_key_exists($productNumber, $this->_errors[$filename]) )
            {
                $this->_errors[$filename][$productNumber][] = $error;
            } else
            {
                $this->_errors[$filename][$productNumber] = array($error);
            }
            $this->addError($error);
        }
    }
    
    /**
     * Log a message to the logfile and output it to stdout.
     * @param string $message
     * @param int $level
     * @return C4B_XmlImport_Model_MessageHandler
     */
    protected function _addMessage($message, $level)
    {
        $this->_dateTime->setTimestamp(time());
        echo "[{$this->_dateTime->format('Y-m-d H:i:s')}] {$message}\n";
        Mage::log($message, $level, self::DEFAULT_LOG_FILE_NAME, true);
        return $this;
    }

    /**
     * Get start time for use in email template.
     * @return string
     */
    public function getStartTime()
    {
        return $this->getData('start_timestamp');
    }
    
    /**
     * Things to do after import process is done.
     * @return C4B_XmlImport_Model_MessageHandler
     */
    public function finalizeResults()
    {
        $this->setTimeTaken(microtime(true) - $this->getStartMicroTime());
        $this->setMemoryUsed(Mage::helper('xmlimport')->humanSize(memory_get_usage() - $this->getStartMem()));
    
        $this->addNotice('Import process done');
        $this->addNotice("Time taken: {$this->getTimeTaken()} s");
        $this->addNotice("Memory used: {$this->getMemoryUsed()}");
        $this->_sendErrorReport();
        $this->_sendAttributesReport();
        return $this;
    }
    
    /**
     * Log start of import process
     * @return C4B_XmlImport_Model_Importer_Result
     */
    public function startReport()
    {
        $this->setStartMicroTime( microtime(true) );
        $this->setStartTimestamp( strftime('%Y-%m-%d %H:%M:%S'),$this->getStartMicroTime() );
        $this->setStartMem(memory_get_usage());
        $this->addNotice('Import started.');
        $this->addNotice('System process ID: ' . getmypid());
        return $this;
    }
    
    /**
     * Reterive and format the recipients from store config or false if there aren't any.
     * @param string $path
     * @return array|boolean
     */
    protected function _getRecipients($path)
    {
        $recipients = unserialize(Mage::getStoreConfig($path));
        if($recipients !== null && is_array($recipients) && count($recipients) > 0)
        {
            $transformed = array();
            foreach($recipients as $recipient)
            {
                $transformed['names'][] = $recipient['name'];
                $transformed['emails'][] = $recipient['email'];
            }
            return $transformed;
        }
        return false;
    }
    
    /**
     * Send an email with error report for the product import to recipients who are configured.
     * @return C4B_XmlImport_Model_MessageHandler
     */
    protected function _sendErrorReport()
    {
        $recipients = $this->_getRecipients(self::XML_PATH_NOTIFICATIONS_IMPORT_ERROR_RECIPIENTS);
        if( count($this->_errors) == 0 || !$recipients)
        {
            return $this;
        }
        $results = array(
                    'start_time'                 => $this->getStartTimestamp(),
                    'time_taken'                 => $this->getTimeTaken(),
                    'memory_used'                 => $this->getMemoryUsed(),
                    'count_import_files'         => $this->getNumberOfImportFiles(),
                    'count_error_import_files'     => count($this->_errors),
                    'errors'                    => $this->_errors
                );
        /* @var $email Mage_Core_Model_Email_Template */
        $email = Mage::getModel('core/email_template');
        $email->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_NOTIFICATIONS_IMPORT_ERROR_EMAIL_TEMPLATE),
                'general',
                $recipients['emails'],
                $recipients['names'],
                array('results' => $results, 'startTime' => $this->getStartTimestamp())
        );
    }
    
    /**
     * Send an email with missing attributes report to recipients who are configured.
     */
    protected function _sendAttributesReport()
    {
        /* @var $attributeCreator C4B_XmlImport_Model_AttributeCreator */
        $attributeCreator = Mage::getSingleton('xmlimport/attributeCreator');
        $missingAttributes = $attributeCreator->getMissingAttributes();
        
        $recipients = $this->_getRecipients(self::XML_PATH_NOTIFICATIONS_MISSING_ATTRIBUTES_RECIPIENTS);
        
        if( count($missingAttributes) == 0 || !$recipients)
        {
            return $this;
        }
        
        /* @var $email Mage_Core_Model_Email_Template */
        $email = Mage::getModel('core/email_template');
        $email->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_NOTIFICATIONS_MISSING_ATTRIBUTES_EMAIL_TEMPLATE),
                'general',
                $recipients['emails'],
                $recipients['names'],
                array('attributes' => $missingAttributes, 
                      'attributes_created' => Mage::getStoreConfig(C4B_XmlImport_Model_AttributeCreator::XML_PATH_PREPROCESSING_CREATE_ATTRIBUTES),
                      'startTime' => $this->getStartTimestamp()
                )
        );
    }
}
