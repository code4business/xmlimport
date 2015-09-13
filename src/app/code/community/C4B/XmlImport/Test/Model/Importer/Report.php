<?php

/**
 * Test for importer report class
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 **/
class C4B_XmlImport_Test_Model_Importer_Report extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function test_reportedNoticeIsLogged()
    {
        /** @var C4B_XmlImport_Model_Importer_Report $importerReport */
        $importerReport = Mage::getSingleton('xmlimport/importer_report');

        $importerReport->notice('Reported info message');

        $this->assertFileExists( Mage::getBaseDir('log') . DS . C4B_XmlImport_Model_Importer_Report::LOG_FILE_NAME );
    }

    /**
     * @test
     */
    public function test_reportedErrorIsLogged()
    {
        /** @var C4B_XmlImport_Model_Importer_Report $importerReport */
        $importerReport = Mage::getSingleton('xmlimport/importer_report');

        $importerReport->error('General error');
        $importerReport->error('General error2');

        $reflector = new ReflectionClass( 'C4B_XmlImport_Model_Importer_Report' );
        $errorsProperty =  $reflector->getProperty('_error');
        $errorsProperty->setAccessible(true);
        $loggedErrors = $errorsProperty->getValue($importerReport);

        $this->assertArrayHasKey('general', $loggedErrors);
        $this->assertContains('General error', $loggedErrors['general']);
        $this->assertContains('General error2', $loggedErrors['general']);
    }

    /**
     * @test
     */
    public function test_errorIsReportedInGivenArea()
    {
        /** @var C4B_XmlImport_Model_Importer_Report $importerReport */
        $importerReport = Mage::getSingleton('xmlimport/importer_report');

        $importerReport->error('Error in file', 'importfile.xml');

        $reflector = new ReflectionClass( 'C4B_XmlImport_Model_Importer_Report' );
        $errorsProperty =  $reflector->getProperty('_error');
        $errorsProperty->setAccessible(true);
        $loggedErrors = $errorsProperty->getValue($importerReport);

        $this->assertArrayHasKey('importfile.xml', $loggedErrors);
        $this->assertContains('Error in file', $loggedErrors['importfile.xml']);
    }

    /**
     * @test
     */
    public function test_messagesGetPrintedToStdout()
    {
        /** @var C4B_XmlImport_Model_Importer_Report $importerReport */
        $importerReport = Mage::getSingleton('xmlimport/importer_report');

        ob_clean();
        ob_start();
        $importerReport->notice('Notice not printed to stdout.');
        $importerReport->error('Errot not printed to stdout.');
        $this->assertEmpty( ob_get_clean() );

        $importerReport->setIsMessageToStdout(true);
        ob_start();
        $importerReport->notice('Notice printed to stdout.');
        $importerReport->error('Errot printed to stdout.');
        $this->assertNotEmpty( ob_get_clean() );
    }
}