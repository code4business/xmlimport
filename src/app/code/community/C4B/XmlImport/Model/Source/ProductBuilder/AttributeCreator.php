<?php
/**
 * This class creates missing categories.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Source_ProductBuilder_AttributeCreator
{
    protected $_existingAttributes = array();
    protected $_missingAttributes = array();

    const XML_PATH_PREPROCESSING_CREATE_ATTRIBUTES = 'c4b_xmlimport/preprocessing/create_attributes';
    const XML_PATH_PREPROCESSING_IGNORED_NEW_ATTRIBUTES = 'c4b_xmlimport/preprocessing/ignored_new_attributes';

    const EVENT_MISSING_ATTRIBUTE_CREATED = 'c4b_xmlimport_missing_attribute_created';

    /**
     * Default constructor.
    */
    public function __construct()
    {
        //These attributes have a particular meaning, and don't directly map to one attribute
        $particularAttributes = array(
                '_store', '_attribute_set', '_type', '_category', '_root_category', '_product_websites',
                '_tier_price_website', '_tier_price_customer_group', '_tier_price_qty', '_tier_price_price',
                '_links_related_sku', '_group_price_website', '_group_price_customer_group', '_group_price_price',
                '_links_related_position', '_links_crosssell_sku', '_links_crosssell_position', '_links_upsell_sku',
                '_links_upsell_position', '_custom_option_store', '_custom_option_type', '_custom_option_title',
                '_custom_option_is_required', '_custom_option_price', '_custom_option_sku', '_custom_option_max_characters',
                '_custom_option_sort_order', '_custom_option_file_extension', '_custom_option_image_size_x',
                '_custom_option_image_size_y', '_custom_option_row_title', '_custom_option_row_price',
                '_custom_option_row_sku', '_custom_option_row_sort', '_media_attribute_id', '_media_image', '_media_lable',
                '_media_position', '_media_is_disabled',
                //Stock Data attributes
                'manage_stock', 'use_config_manage_stock', 'qty', 'min_qty', 'use_config_min_qty', 'min_sale_qty','use_config_min_sale_qty',
                'max_sale_qty', 'use_config_max_sale_qty', 'is_qty_decimal', 'backorders', 'use_config_backorders', 'notify_stock_qty',
                'use_config_notify_stock_qty', 'enable_qty_increments', 'use_config_enable_qty_inc', 'qty_increments',
                'use_config_qty_increments', 'is_in_stock', 'low_stock_date', 'stock_status_changed_auto', 'is_decimal_divided'
        );

        $attributesToIgnore = explode(',', Mage::getStoreConfig(static::XML_PATH_PREPROCESSING_IGNORED_NEW_ATTRIBUTES));

        $existingAttributes = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();

        /* @var $existingAttributes Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute_Collection */
        foreach ( $existingAttributes as $attribute )
        {
            $this->_existingAttributes[strtolower($attribute->getAttributeCode())] = 1;
        }
        foreach ( $particularAttributes as $attributeCode )
        {
            $this->_existingAttributes[$attributeCode] = 1;
        }
        foreach ( $attributesToIgnore as $attributeCode )
        {
            $this->_existingAttributes[strtolower($attributeCode)] = 1;
        }


    }

    /**
     * Creates the specified attribute if it doesn't exist.
     * @param string $attributeCode
     * @return C4B_XmlImport_Model_Source_ProductBuilder_AttributeCreator | int
     */
    public function createIfNotExists($attributeCode)
    {
        $attributeCode = strtolower($attributeCode);
        /** @var C4B_XmlImport_Model_Importer_Report $importReport */
        $importReport = Mage::getSingleton('xmlimport/importer_report');

        if( isset($this->_existingAttributes[$attributeCode]) )
        {
            return true;
        }

        $createAttributes = Mage::getStoreConfigFlag(static::XML_PATH_PREPROCESSING_CREATE_ATTRIBUTES);

        if(!$createAttributes)
        {
            if(!array_key_exists($attributeCode, $this->_missingAttributes))
            {
                $importReport->notice("Attribute '{$attributeCode}' does not exist and won't be imported.");
            }
            $this->_missingAttributes[$attributeCode] = 1;
            return false;
        }


        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $newAttribute = Mage::getModel('eav/entity_attribute');
        $newAttribute->setData(array(
                'entity_type_id' => $entityTypeId,
                'attribute_code' => $attributeCode,
                'frontend_label' => $attributeCode,
                'frontend_input' => 'select',
                'backend_type' => $newAttribute->getBackendTypeByInput('select'),
                'source' => 'eav/entity_attribute_source_table',
                'is_global' => '1',
                'is_visible' => '1',
                'is_visible_on_front' => '1',
                'is_user_defined' => '1',
                'is_searchable' => '0',
                'is_filterable' => '0',
                'is_filterable_in_search' => '0',
                'is_comparable' => '1',
                'is_configurable' => '1',
                'apply_to' => 'simple',
                'default' => '0'
        ));

        Mage::dispatchEvent( static::EVENT_MISSING_ATTRIBUTE_CREATED, array('attribute' => $newAttribute) );

        $newAttribute->save();
        $this->_missingAttributes[$attributeCode] = 1;
        $this->_existingAttributes[$attributeCode] = 1;

        $importReport->notice("Created attribute {$attributeCode}.");

        return true;
    }

    /**
     * Get the missing attribute codes.
     * @return array
     */
    public function getMissingAttributes()
    {
        return array_keys( $this->_missingAttributes );
    }
}
