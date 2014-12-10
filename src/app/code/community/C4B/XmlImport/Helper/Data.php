<?php
/**
 * General helper.
 * 
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_utf8ConversionMap = array( 0x0, 0x2FFFF, 0, 0xFFFF );
    /**
     * Return the given nodes first level children as an array. Excludes attributes.
     * Format: 'node_name' => 'node_value'
     * @param SimpleXMLElement $parentNode
     * @return array
     */
    public function xmlChildrenToArray($parentNode)
    {
        $arrayToReturn = array();
        foreach($parentNode as $nodeName => $childNode)
        {
            $arrayToReturn[$nodeName] = $childNode->__toString();
        }
        return $arrayToReturn;
    }
    
    /**
     * Decode the numeric entities in the given string and return it.
     * @param string $encodedString
     * @return string
     */
    public function parseNumericEntities($encodedString)
    {
        return mb_decode_numericentity($encodedString, $this->_utf8ConversionMap, 'UTF-8');
    }
    
    /**
     * Convert size to human readable
     *
     * @param int $number
     * @return string
     */
    public function humanSize($number)
    {
        if ($number < 1000) {
            return sprintf('%d b', $number);
        } else if ($number >= 1000 && $number < 1000000) {
            return sprintf('%.2fKb', $number / 1000);
        } else if ($number >= 1000000 && $number < 1000000000) {
            return sprintf('%.2fMb', $number / 1000000);
        } else {
            return sprintf('%.2fGb', $number / 1000000000);
        }
    }
    
    /**
     * Gets the selected directory path and creates it if it doesn't exist.
     * @param string $type
     * @return string
     */
    public function getDirectory($type = 'base')
    {
        /* @var $dirCreator Mage_Core_Model_Config_Options */
        $dirCreator = Mage::getModel('core/config_options');
    
        $path = Mage::getBaseDir();
        switch($type)
        {
            case 'success':
                $path .= DS . Mage::getStoreConfig(C4B_XmlImport_Model_Importer::XML_PATH_IMPORT_SUCCESS_DIRECTORY);
                break;
            case 'error':
                $path .= DS . Mage::getStoreConfig(C4B_XmlImport_Model_Importer::XML_PATH_IMPORT_ERROR_DIRECTORY);
                break;
            case 'base':
                $path .= DS . Mage::getStoreConfig(C4B_XmlImport_Model_Importer::XML_PATH_IMPORT_DIRECTORY);
            default:
                break;
        }
        $dirCreator->createDirIfNotExists($path);
        return $path;
    }
    
    /**
     * Format the errors to a single array
     * @param array $errors
     */
    public function formatErrors($errors)
    {
        $messages = array();
        $n = 0;
        foreach ($errors as $message => $rows) 
        {
            $messages[]= sprintf("\t%d: %s (rows: %s)", ++$n, $message, join(',', $rows));
        }
        return join("\n", $messages);
    }
}