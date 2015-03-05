<?php
/**
 * This class extracts attribute values for a single product and saves them as array.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Products_ProductBuilder
{
    const EVENT_NAME_AFTER_SIMPLE_DATA = 'c4b_xmlimport_after_simple_data';
    const EVENT_NAME_AFTER_COMPLEX_DATA = 'c4b_xmlimport_after_complex_data';
    
    protected $_errors = array();
    
    /**
     * Validate and extract product data from a single node. Returns an array of product data or null if errors occured.
     * @param DOMNode $domNode
     * @return array|null
     */
    public function getProductData($domNode)
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $node = $doc->importNode($domNode, true);
        $doc->appendChild( $node );
        
        $xmlProductNode = simplexml_import_dom( $node );
        
        $this->_errors = array();
        if( !property_exists( $xmlProductNode, 'simple_data' ) && !property_exists($xmlProductNode, 'stores') )
        {
            $this->_errors[] = 'Missing <simple_data> or <stores> node.';
            return null;
        }
        
        $productData = $this->_extractSimpleData($xmlProductNode, $this->_extractStoreCodes($xmlProductNode) );
        if($productData == null)
        {
            $this->_errors[] = 'Invalid simple data.';
            return null;
        }
        $productData = $this->_afterSimpleData($productData);
        if($productData == null)
        {
            return null;
        }
        
        $complexAttributeData = $this->_extractComplexData( $xmlProductNode );
        if($complexAttributeData == null)
        {
            $this->_errors[] = 'Invalid complex data.';
            return null;
        }
        $complexAttributeData = $this->_afterComplexData($complexAttributeData);
        
        if( !is_null($complexAttributeData) && !empty($complexAttributeData) )
        {
            $productData['default'] = array_merge($productData['default'], $complexAttributeData);
        }
        
        return $this->_formatData($productData);
    }
    
    /**
     * Return all error messages that were loged.
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Reformat data to non-asociative array.
     * @return array
     */
    protected function _formatData($productData)
    {
        $productStoreConfigs = array();
        
        foreach($productData as $store => $productStoreConfig)
        {
            $productStoreConfigs[] = $productStoreConfig;
        }
        
        return $productStoreConfigs;
    }
    
    /**
     * Get store view codes used in the XML and uses only the ones registered in the system
     * @param SimpleXMLElement $xmlProductNode
     * @return array|null product stores data;
     */
    protected function _extractStoreCodes( $xmlProductNode )
    {
        $systemStores = Mage::getModel('core/store')->getCollection();
        $systemStoreCodes = array('default');
        foreach ($systemStores as $singleStore) {
            $systemStoreCodes[] = $singleStore->getCode();
        }
        $productData['default'] = array();
        
        foreach($xmlProductNode->stores->children() as $storeNode){
            $xmlStoreCode = $storeNode[0]->__toString();
            if(in_array($xmlStoreCode, $systemStoreCodes)) {
                $productData[$xmlStoreCode] = Array();
            }
            else {
                $this->_errors[] = "The store '{$xmlStoreCode}' does not exist in the system. Data regarding this store will not be imported.";
            }
        }
        
        return $productData;
    }
    
    /**
     * Extract simple_data values from currently loaded xml.
     * @param SimpleXMLElement $xmlProductNode
     * @param array $productData
     * @return array|null product simple data
     */
    protected function _extractSimpleData($xmlProductNode, $productData)
    {
        /* @var $attributeCreator C4B_XmlImport_Model_ProductCreator */
        $attributeCreator = Mage::getSingleton('xmlimport/attributeCreator');
        
        if( !$xmlProductNode->simple_data->children() )
        {
            return null;
        }
        /* @var $simpleAttributeNode SimpleXMLElement */
        foreach($xmlProductNode->simple_data->children() as $simpleAttributeNode)
        {
            foreach($productData as $store => $productStoreConfig)
            {
                if(!array_key_exists($store, $simpleAttributeNode)){
                    continue;
                }

                $value = $this->_parseValue($simpleAttributeNode->$store->__toString());
                if($value === false){
                    continue;
                }

                $importAttribute = $attributeCreator->createIfNotExists($simpleAttributeNode->getName());
                if($importAttribute){
                    $productData[$store][$simpleAttributeNode->getName()] = $value;
                }
            }
        }
        return $productData;
    }
    
    /**
     * Parse the string to see if it contains a value. Returns false if the input string is empty
     * @param string $stringValue
     * @return boolean|string
     */
    protected function _parseValue($stringValue)
    {
        $stringValue = trim($stringValue);
        if( strlen($stringValue) === 0 )
        {
            return false;
        }
        
        return Mage::helper('xmlimport')->parseNumericEntities($stringValue);
    }
    
    /**
     * Processing after all attributes were collected.
     * @param array $productData
     * @return array|null product simple data
     */
    protected function _afterSimpleData($productData)
    {
        foreach($productData as $storeCode => $storeSpecificData)
        {
            if(count($storeSpecificData) == 0)
            {
                unset($productData[$storeCode]);
                continue;
            }
            if(!array_key_exists('sku', $storeSpecificData))
            {
                $productData[$storeCode]['sku'] = null;
                $productData[$storeCode]['_store'] = $storeCode;
            }
        }
        
        $transport = new Varien_Object();
        $transport->setData('product_data',$productData);
        $transport->setData('errors',array());
        $transport->setData('invalidate_data',false);
        
        Mage::dispatchEvent(self::EVENT_NAME_AFTER_SIMPLE_DATA, array('transport' => $transport));
        $productData = $transport->getData('product_data');
        $this->_errors = array_merge( $this->_errors,$transport->getData('errors') );
        
        if( $transport->getData('invalidate_data') == true )
        {
            return null;
        }
    
        return $productData;
    }
    
    /**
     * Extract complex data values from currently loaded xml node.
     * @param SimpleXMLElement $xmlProductNode
     * @return array|null product complex data
     */
    protected function _extractComplexData($xmlProductNode)
    {
        if(property_exists($xmlProductNode, 'complex_data') && !$xmlProductNode->complex_data->children())
        {
            return array();
        }
        $productData = array();
        /* @var $complexAttribute C4B_XmlImport_Model_Products_ComplexAttribute */
        $complexAttribute = Mage::getModel('xmlimport/products_complexAttribute');
        /* @var $complexDataNode SimpleXMLElement */
        $complexAttributePosition = 0;
        foreach($xmlProductNode->complex_data->children() as $complexDataNode)
        {
            $complexAttributePosition++;
            $complexAttributeData = $complexAttribute->getComplexAttributeData($complexDataNode);
            if( is_null($complexAttributeData) )
            {
                $this->_errors[] = "Complex attribute at position {$complexAttributePosition} is invalid.";
                return null;
            }
            $productData = array_merge($productData, $complexAttributeData);
        }
        return $productData;
    }
    
    /**
     * Additional altering of collected complex data.
     * @param $productComplexData
     * @return array|null product data
     */
    protected function _afterComplexData($productComplexData)
    {
        $productComplexData = $this->_createNewCategories($productComplexData);

        $transport = new Varien_Object();
        $transport->setData('product_complex_data',$productComplexData);
        $transport->setData('errors',array());
        $transport->setData('invalidate_data',false);
        
        Mage::dispatchEvent(self::EVENT_NAME_AFTER_COMPLEX_DATA,array('transport' => $transport));
        $this->_errors = array_merge( $this->_errors,$transport->getData('errors') );
        
        if( $transport->getData('invalidate_data') == true )
        {
            return null;
        }
        
        return $transport->getData('product_complex_data');
    }
    
    /**
     * Create new categories if configured to do so.
     * @param array $productComplexData
     * @return array|null product complex data
     */
    protected function _createNewCategories($productComplexData)
    {
        if( !array_key_exists('_category', $productComplexData) 
                || Mage::getStoreConfig(C4B_XmlImport_Model_Importer::XML_PATH_PREPROCESSING_CREATE_CATEGORIES) == false )
        {
            return $productComplexData;
        }
        /* @var $categoryCreator C4B_XmlImport_Model_CategoryCreator */
        $categoryCreator = Mage::getSingleton('xmlimport/categoryCreator');
        foreach( $productComplexData['_category'] as $key => $category )
        {
            if( $categoryCreator->createIfItNotExists($category) == null )
            {
                unset($productComplexData['_category'][$key]);
            }
            $this->_errors = array_merge( $this->_errors, $categoryCreator->getErrors() );
        }
        return $productComplexData;
    }
}
