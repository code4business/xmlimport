<?php
/**
 * This class represents a single <enum> xml node. It is responsible for extracting and formating data to an array element(s).
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Products_ComplexAttribute
{    
    /**
     * get complex attribute value from the given node.
     * @param SimpleXMLElement $complexDataXmlNode
     * @return null|array
     */
    public function getComplexAttributeData($complexDataXmlNode)
    {
        if( !$this->_validateStructure($complexDataXmlNode) )
        {
            return null;
        }
        return $this->_processXmlNode($complexDataXmlNode);
    }
    
    /**
     * Process the xml node, extract and format values from it and return it.
     * @param SimpleXMLElement $complexDataXmlNode
     * @return array
     */
    protected function _processXmlNode($complexDataXmlNode)
    {
        $attributeData = array();
        /* @var $enumItem SimpleXMLElement */
        foreach ($complexDataXmlNode->children() as $enumItem)
        {
            foreach($enumItem as $attributeValue)
            {
                $value = trim(Mage::helper('xmlimport')->parseNumericEntities( $attributeValue->__toString() ));
                if(array_key_exists($attributeValue->getName(), $attributeData))
                {
                    array_push($attributeData[$attributeValue->getName()], $value);
                } else
                {
                    $attributeData[$attributeValue->getName()] = array($value);
                }
            }
        }
        return $attributeData;
    }
    
    /**
     * Validate if children have same number of children.
     * @param SimpleXMLElement $complexDataXmlNode
     * @return boolean
     */
    protected function _validateStructure($complexDataXmlNode)
    {
        if($complexDataXmlNode->count() == 0)
        {
            return true;
        }
        $expectedChildren = null;
        $expectedChildrenCount = 0;
        foreach ($complexDataXmlNode->children() as $enumItem)
        {
            if($expectedChildren == null)
            {
                /* @var $enumItemChild SimpleXMLElement */
                foreach($enumItem->children() as $nodeName => $enumItemChild)
                {
                    $expectedChildrenCount++;
                    $expectedChildren[$nodeName] = true; 
                }
                continue;
            }
            if($enumItem->count() != $expectedChildrenCount)
            {
                return false;
            }
            $compareFrom = Mage::helper('xmlimport')->xmlChildrenToArray($enumItem);
            if(count(array_diff_key($compareFrom, $expectedChildren)) != 0)
            {
                return false;
            }
        }
        return true;
    }
}