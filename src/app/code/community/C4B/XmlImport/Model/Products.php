<?php
/**
 * This class load the xml and gets the data for each product in the xml file.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Products
{    
    const VALIDATION_RESULT_FILE_ERROR = 0;
    const VALIDATION_RESULT_OK = 1;
    const VALIDATION_RESULT_NO_ROOT_NODE = 2;
    const VALIDATION_RESULT_NO_PRODUCT_NODES = 3;
    
    const XML_NODE_NAME_ROOT = 'products';
    const XML_NODE_NAME_PRODUCT = 'product';
    
    /**
     * Validate XML file for syntax errors and basic node presence.
     * @param string $filePath
     * @return string
     */
    public function validateFile($filePath)
    {
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        try 
        {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($filePath);
            $messageHandler->addErrorsForFile( basename($filePath), ' ', $messageHandler->formatXmlParseMessage(libxml_get_errors()) );
        } catch(Exception $e)
        {
            return self::VALIDATION_RESULT_FILE_ERROR;
        }
        if(!$xml)
        {
            return self::VALIDATION_RESULT_NO_ROOT_NODE;
        }
        if(!property_exists($xml, self::XML_NODE_NAME_PRODUCT))
        {
            return self::VALIDATION_RESULT_NO_PRODUCT_NODES;
        }
        
        return self::VALIDATION_RESULT_OK;
    }
    
    /**
     * Processes given xml file by iterating over product nodes and extracting data into array
     * @param string $filePath
     * @return boolean
     */
    public function processFile($filePath)
    {
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        /* @var $productBuilder C4B_XmlImport_Model_Products_ProductBuilder */
        $productBuilder = Mage::getModel('xmlimport/products_productBuilder');
        $productNodePosition = 0;
        $xmlReader = new XMLReader();
        $xmlReader->open($filePath);
        $products = array();
        while($xmlReader->read())
        {
            if($xmlReader->nodeType != XMLReader::ELEMENT || $xmlReader->name != self::XML_NODE_NAME_PRODUCT)
            {
                continue;
            }
            $productNodePosition++;
            $productData = $productBuilder->getProductData( $xmlReader->expand() );
            if( count($productBuilder->getErrors()) > 0 )
            {
                $messageHandler->addError("Product at position {$productNodePosition} has errors:");
            }
            if($productData == null)
            {
                $messageHandler->addError('Product will not be imported');
            } else
            {
                foreach ($productData as $productDataRow)
                {
                    $products[] = $productDataRow;
                }
            }
            $messageHandler->addErrorsForFile( basename($filePath), $productNodePosition, $productBuilder->getErrors() );
        }
        
        return $products;
    }
}