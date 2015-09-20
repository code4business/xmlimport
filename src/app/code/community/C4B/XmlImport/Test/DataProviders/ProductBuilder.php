<?php

/**
 * Data provider class, central point for all test data related to productBuilder.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 **/
class C4B_XmlImport_Test_DataProviders_ProductBuilder
{
    /**
     * Get a DOMElement, representing a simple valid product node.
     *
     * @return DOMElement
     */
    public function getMinimal()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version='1.0' encoding='UTF-8'?>
            <products>
                <product>
                    <simple_data>
                        <sku><default>SIMPLE-1</default></sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name><default>Import Simple product</default></name>
                        <price><default>10</default></price>
                        <description><default>Test description</default></description>
                        <short_description><default>Test short description</default></short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                    </simple_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing a simple valid product node with translations for name and
     * short/normal descriptions.
     *
     * @return DOMElement
     */
    public function getMinimalTranslatedDeFr()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <stores>
                        <item>default</item>
                        <item>german</item>
                        <item>french</item>
                    </stores>
                    <simple_data>
                        <sku><default>SIMPLE-1</default></sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name>
                            <default>Import Simple product</default>
                            <german>Import German Einfaches produkt</german>
                            <french>Import produit simple</french>
                        </name>
                        <price><default>10</default></price>
                        <description>
                            <default>Test description</default>
                            <german>Testbeschreibung</german>
                            <french>Description de l'essai</french>
                        </description>
                        <short_description>
                            <default>Test short description</default>
                            <german>Test kurze Beschreibung</german>
                            <french>Essai courte description</french>
                        </short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                    </simple_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing an invalid product node that has no simple data.
     *
     * @return DOMElement
     */
    public function getMissingSimpleData()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><products><product><complex_data></complex_data></product></products>');
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing product node with only store scope data.
     *
     * @return DOMElement
     */
    public function getStoreScopeOnly()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <stores>
                        <item>german</item>
                    </stores>
                    <simple_data>
                        <sku><german>SIMPLE-1</german></sku>
                        <name>
                            <german>Import German Einfaches produkt</german>
                        </name>
                        <description>
                            <german>Testbeschreibung</german>
                        </description>
                        <short_description>
                            <german>Test kurze Beschreibung</german>
                        </short_description>
                    </simple_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing product node with SKU defined in each store.
     *
     * @return DOMElement
     */
    public function getSkuInEachScope()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
            $doc->loadXML(
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <stores>
                        <item>default</item>
                        <item>german</item>
                        <item>french</item>
                    </stores>
                    <simple_data>
                        <sku>
                            <default>SIMPLE-1</default>
                            <german>SIMPLE-1</german>
                            <french>SIMPLE-1</french>
                        </sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name>
                            <default>Import Simple product</default>
                            <german>Import German Einfaches produkt</german>
                            <french>Import produit simple</french>
                        </name>
                        <price><default>10</default></price>
                        <description>
                            <default>Test description</default>
                            <german>Testbeschreibung</german>
                            <french>Description de l'essai</french>
                        </description>
                        <short_description>
                            <default>Test short description</default>
                            <german>Test kurze Beschreibung</german>
                            <french>Essai courte description</french>
                        </short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                    </simple_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing product node where SKU is not defined in the default scope.
     *
     * @return DOMElement
     */
    public function getSkuInStoreScope()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <stores>
                        <item>default</item>
                        <item>german</item>
                        <item>french</item>
                    </stores>
                    <simple_data>
                        <sku>
                            <default>SIMPLE-1</default>
                            <german>SIMPLE-1</german>
                            <french>SIMPLE-1</french>
                        </sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name>
                            <default>Import Simple product</default>
                            <german>Import German Einfaches produkt</german>
                            <french>Import produit simple</french>
                        </name>
                        <price><default>10</default></price>
                        <description>
                            <default>Test description</default>
                            <german>Testbeschreibung</german>
                            <french>Description de l'essai</french>
                        </description>
                        <short_description>
                            <default>Test short description</default>
                            <german>Test kurze Beschreibung</german>
                            <french>Essai courte description</french>
                        </short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                    </simple_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing a valid product node with simple and complex data.
     *
     * @return DOMElement
     */
    public function getSimpleComplex()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <stores>
                        <item>default</item>
                        <item>german</item>
                        <item>french</item>
                    </stores>
                    <simple_data>
                        <sku><default>SIMPLE-1</default></sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name>
                            <default>Import Simple product</default>
                            <german>Import German Einfaches produkt</german>
                            <french>Import produit simple</french>
                        </name>
                        <price><default>10</default></price>
                        <description>
                            <default>Test description</default>
                            <german>Testbeschreibung</german>
                            <french>Description de l'essai</french>
                        </description>
                        <short_description>
                            <default>Test short description</default>
                            <german>Test kurze Beschreibung</german>
                            <french>Essai courte description</french>
                        </short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                        <image><default>images/image1.jpg</default></image>
                    </simple_data>
                    <complex_data>
                        <enum>
                            <item>
                               <_product_websites>base</_product_websites>
                            </item>
                        </enum>
                        <enum>
                            <item>
                               <_category>Category</_category>
                            </item>
                            <item>
                               <_category>Category/Subcategory</_category>
                            </item>
                        </enum>
                        <enum>
                            <item>
                               <_media_image>images/image1.jpg</_media_image>
                               <_media_is_disabled>0</_media_is_disabled>
                            </item>
                            <item>
                               <_media_image>images/image2.jpg</_media_image>
                               <_media_is_disabled>0</_media_is_disabled>
                            </item>
                        </enum>
                    </complex_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }

    /**
     * Get a DOMElement, representing a product node with invalid complex_data attribute.
     *
     * @return DOMElement
     */
    public function getComplexMismatched()
    {
        $doc = new DOMDocument('1.', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->loadXML(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <products>
                <product>
                    <simple_data>
                        <sku><default>SIMPLE-1</default></sku>
                        <_attribute_set><default>Default</default></_attribute_set>
                        <_type><default>simple</default></_type>
                        <name>
                            <default>Import Simple product</default>
                        </name>
                        <price><default>10</default></price>
                        <description>
                            <default>Test description</default>
                        </description>
                        <short_description>
                            <default>Test short description</default>
                        </short_description>
                        <status><default>1</default></status>
                        <qty><default>50</default></qty>
                        <is_in_stock><default>1</default></is_in_stock>
                        <image><default>images/image1.jpg</default></image>
                    </simple_data>
                    <complex_data>
                        <enum>
                            <item>
                               <_product_websites>base</_product_websites>
                            </item>
                        </enum>
                        <enum>
                            <item>
                               <_category>Category</_category>
                            </item>
                            <item>
                               <_category>Category/Subcategory</_category>
                            </item>
                        </enum>
                        <enum>
                            <item>
                               <_media_image>images/image1.jpg</_media_image>
                            </item>
                            <item>
                               <_media_image>images/image2.jpg</_media_image>
                               <_media_is_disabled>0</_media_is_disabled>
                            </item>
                        </enum>
                    </complex_data>
                </product>
            </products>"
        );
        return $doc->documentElement->firstChild;
    }
}