<?php
/**
 * Helper for setting and relasing DB Mutex locks.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglic <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    const LOCK_SETTING_TIMEOUT = '5';
    
    /**
     * Set named lock in DB
     * @param string $lockName
     * @return bool
     */
    public function setNamedLock($lockName)
    {
        return (bool)$this->_getWriteAdapter()->query('SELECT GET_LOCK(?, ?);', array($lockName, self::LOCK_SETTING_TIMEOUT))->fetchColumn();
    }
    
    /**
     * Release named lock in DB
     * @param string $lockName
     * @return bool
     */
    public function releaseNamedLock($lockName)
    {
        return (bool)$this->_getWriteAdapter()->query('SELECT RELEASE_LOCK(?);', array($lockName))->fetchColumn();
    }
}