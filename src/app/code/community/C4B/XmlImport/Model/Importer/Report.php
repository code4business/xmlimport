<?php

/**
 * Model responsible for reporting messages during import.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 *
 * @method int getStartMicroTime()
 * @method C4B_XmlImport_Model_Importer_Report setStartMicroTime(int)
 * @method string getStartTimestamp()
 * @method C4B_XmlImport_Model_Importer_Report setStartTimestamp(string)
 * @method string getTimeTaken()
 * @method C4B_XmlImport_Model_Importer_Report setTimeTaken(string)
 * @method int getImportFileCount()
 */
class C4B_XmlImport_Model_Importer_Report extends Varien_Object
{
    const LOG_FILE_NAME = 'import.log';

    const XML_PATH_NOTIFICATIONS_REPORT_RECIPIENTS = 'c4b_xmlimport/notifications/report_recipients';
    const XML_PATH_NOTIFICATIONS_REPORT_EMAIL_TEMPLATE = 'c4b_xmlimport/notifications/report_template';

    /**
     * @var string
     */
    protected $_logFile;
    /**
     * Reported errors
     * @var array
     */
    protected $_error;
    /**
     * Datetime object with configured timezone
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * Default constructor, prepares state.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_error = array();
        $this->_dateTime = new DateTime('now', new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
        $this->_logFile = static::LOG_FILE_NAME;
    }

    /**
     * Set a custom logfile name
     *
     * @param $logFile string
     */
    public function setLogfile($logFile)
    {
        $this->_logFile = $logFile;
    }

    /**
     * Prepare starting information
     */
    public function start()
    {
        $this->_dateTime->setTimestamp(time());
        $this->setData('has_errors', false);
        $this->setData('import_file_count', 0);
        $this->setData('start_timestamp', $this->_dateTime->format('Y-m-d H:i:s'));
        $this->setData('start_micro_time', microtime(true));

        $this->notice('Starting import process.');
        $this->notice('System process ID: ' . getmypid());
    }

    /**
     * Report notice(s) in specific area
     *
     * @param string|array $message message(s)
     * @param string $area
     */
    public function notice($message, $area = 'general')
    {
        $this->_message($message, 'notice', Zend_Log::NOTICE, $area);
    }

    /**
     * Report error(s) in specific area
     *
     * @param string|array $message message(s)
     * @param string $area
     */
    public function error($message, $area = 'general')
    {
        $this->_message($message, 'error', Zend_Log::ERR, $area);

        if( !isset($this->_error[$area]) && !empty($message) )
        {
            $this->_error[$area] = array();
        }

        if( !is_array($message) )
        {
            $message = array($message);
        }

        foreach($message as $singleMessage)
        {
            $this->_error[$area][] = $singleMessage;
        }
    }

    /**
     * Mark end of report, prepare and calculate information and send an error report per email if any were encountered
     * and at least one email is configured
     */
    public function end()
    {
        $this->setData('time_taken', round(microtime(true) - $this->getStartMicroTime(), 2));
        $this->notice("Time taken: {$this->getTimeTaken()} s");
        $this->notice('Import process done.');

        $this->_sendReportIfNecessary();
    }

    /**
     * Set the number of import files.
     *
     * @param $count
     */
    public function setImportFileCount($count)
    {
        $this->setData('import_file_count', $count);
        $this->notice("Number of files found in the import directory: {$count}");
    }

    /**
     * Report a message
     *
     * @param string|array $message message(s)
     * @param $type string (error|notice)
     * @param $logLevel Zend_Log
     * @param $area
     */
    protected function _message($message, $type, $logLevel, $area)
    {
        if (is_array($message))
        {
            foreach ($message as $singleMessage)
            {
                $this->_message($singleMessage, $type, $logLevel, $area);
            }
        } else
        {
            $this->_dateTime->setTimestamp(time());
            echo "[{$this->_dateTime->format('Y-m-d H:i:s')}] {$message}\n";
            Mage::log($message, $logLevel, $this->_logFile);
        }
    }

    /**
     * Send an email to configured recipients about import errors and/or missing attributes.
     *
     * @return C4B_XmlImport_Model_Importer_Report
     */
    protected function _sendReportIfNecessary()
    {
        $recipientsRaw = unserialize(Mage::getStoreConfig(static::XML_PATH_NOTIFICATIONS_REPORT_RECIPIENTS));
        $recipients = array();
        foreach($recipientsRaw as $recipient)
        {
            $recipients['names'][] = $recipient['name'];
            $recipients['emails'][] = $recipient['email'];
        }

        $missingAttributes = Mage::getSingleton('xmlimport/attributeCreator')->getMissingAttributes();

        if( (count($this->_error) == 0 && count($missingAttributes) == 0) || count($recipients) == 0)
        {
            return $this;
        }

        $results = new Varien_Object(array(
            'start_time'                 => $this->getStartTimestamp(),
            'time_taken'                 => $this->getTimeTaken(),
            'count_import_files'         => $this->getImportFileCount(),
            'count_error_import_files'     => count($this->_error),
            'errors'                    => $this->_error,
            'missing_attributes'        => $missingAttributes,

        ));
        /* @var $email Mage_Core_Model_Email_Template */
        $email = Mage::getModel('core/email_template');
        $email->sendTransactional(
            Mage::getStoreConfig(static::XML_PATH_NOTIFICATIONS_REPORT_EMAIL_TEMPLATE),
            'general',
            $recipients['emails'],
            $recipients['names'],
            array('results' => $results, 'startTime' => $this->getStartTimestamp())
        );
    }
}