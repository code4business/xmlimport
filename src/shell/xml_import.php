<?php

require_once 'abstract.php';

/**
 * Shell script to start the product import from XML files located in the configured import directory.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Importer extends Mage_Shell_Abstract {

    /**
     * Start import process
     *
     */
    public function run()
    {
         /* @var $importer C4B_XmlImport_Model_Importer */
         $importer = Mage::getModel('xmlimport/importer');

        if( $this->getArg('v') )
        {
            $importer->setPrintMessageToStdout(true);
        }

         $importer->run();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php xml_import.php -- [OPTIONS]

  -v  Print messages to stdout

USAGE;
    }
}

$shell = new C4B_XmlImport_Importer();
$shell->run();
