<?php
/**
 * This setup configures the Avs_FastXmlImport to use nested arrays and adds transactional email email tempaltes
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->setConfigData('fastsimpleimport/general/support_nested_arrays',true);

/* @var $mailTemplate Mage_Core_Model_Email_Template */
$errorMailTemplate = Mage::getModel('core/email_template')->loadByCode('Product Import Error Report');
if( is_null($errorMailTemplate->getId()) )
{
    $errorMailTemplate->addData(array(
            'template_code' => 'Product Import Error Report',
            'template_text' => '{{block type="xmlimport/importerror" result_data=$results start_time=$startTime}}',
            'template_type' => 2, //html
            'template_subject' => 'Errors during product import on  {{var startTime}}',
            'template_styles'    => 'body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }',
            'added_at' => time()
    ));
    $errorMailTemplate->save();
}
$installer->setConfigData(C4B_XmlImport_Model_Importer_Report::XML_PATH_NOTIFICATIONS_IMPORT_ERROR_EMAIL_TEMPLATE, $errorMailTemplate->getId());

/* @var $mailTemplate Mage_Core_Model_Email_Template */
$missingAttributesEmailTemplate = Mage::getModel('core/email_template')->loadByCode('Product Import Missing Attributes Report');
if( is_null($missingAttributesEmailTemplate->getId()) )
{
    $missingAttributesEmailTemplate->addData(array(
            'template_code' => 'Product Import Missing Attributes Report',
            'template_text' => '{{block type="xmlimport/missingattributes" attributes=$attributes attributes_created=$attributes_created}}',
            'template_type' => 2, //html
            'template_subject' => 'Missing attributes during product import on {{var startTime}}',
            'template_styles'    => 'body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }',
            'added_at' => time()
    ));
    $missingAttributesEmailTemplate->save();
}
$installer->setConfigData(C4B_XmlImport_Model_Importer_Report::XML_PATH_NOTIFICATIONS_MISSING_ATTRIBUTES_EMAIL_TEMPLATE, $missingAttributesEmailTemplate->getId());

$installer->endSetup();