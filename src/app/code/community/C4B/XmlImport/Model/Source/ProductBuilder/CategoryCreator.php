<?php
/**
 * This class handles creation of missing categories.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 */
class C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator
{
    const EVENT_MISSING_CATEGORY_CREATED = 'c4b_xmlimport_missing_category_created';

    const XML_PATH_ROOT_CATEGORY_ID = 'c4b_xmlimport/preprocessing/root_category';

    /**
     * All existing categories
     *
     * @var array [NAME_PATH => ID]
     */
    protected $_categories = array();
    /**
     * Reported messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->_initCategories();
    }

    /**
     * Create the specified category and any missing parents from category name path.
     *
     * @param string $categoryNamePath
     * @return boolean
     */
    public function createIfItNotExists($categoryNamePath)
    {
        $this->_messages = array();
        if( isset($this->_categories[$categoryNamePath]) )
        {
            return true;
        }

        $categoryPathParts = explode('/', $categoryNamePath);

        $currentCategoryPath = '';
        $currentCategoryPathIds = '1/' . Mage::getStoreConfig(static::XML_PATH_ROOT_CATEGORY_ID);

        foreach($categoryPathParts as $categoryName)
        {
            $currentCategoryPath = $currentCategoryPath . '/' . $categoryName;

            if( empty($categoryName) )
            {
                $this->_messages[] = array(
                    'message' => "Category [{$categoryNamePath}] can not have empty path parts.",
                    'type' => 'error'
                );
                return false;
            }

            if( !isset($this->_categories[$currentCategoryPath]) )
            {
                /* @var $category Mage_Catalog_Model_Category */
                $category = Mage::getModel('catalog/category');
                $category->setName($categoryName);
                $category->setIsActive(true);
                $category->setPath($currentCategoryPathIds);
                $category->setDisplayMode( Mage_Catalog_Model_Category::DM_PRODUCT );

                Mage::dispatchEvent( static::EVENT_MISSING_CATEGORY_CREATED, array('category' => $category) );

                try {
                    $category->save();
                    $this->_messages[] = array(
                        'message' => "Created category {$categoryName}",
                        'type' => 'notice'
                    );
                } catch (Exception $e)
                {
                    Mage::logException($e);
                    $this->_messages[] = array(
                        'message' => "Category with name {$categoryName} and path {$currentCategoryPath} can not be saved.",
                        'type' => 'error'
                    );
                    return false;
                }

                $this->_categories[$currentCategoryPath] = $category->getId();
            }
            $currentCategoryPathIds = $currentCategoryPathIds . "/{$this->_categories[$currentCategoryPath]}";
        }
        return true;
    }

    /**
     * Get the reported messages.
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Get the category ID from category path or false if it doesn't exist.
     *
     * @param $categoryPath
     * @return bool|int
     */
    public function getCategoryIdFromPath($categoryPath)
    {
        return isset($this->_categories[$categoryPath]) ? $this->_categories[$categoryPath] : false;
    }

    /**
     * Initialize an array with all category paths. Code taken from Avs_FastSimpleImport
     * @see AvS_FastSimpleImport_Model_Import_Entity_Product::_initCategories()
     */
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        foreach ($collection as $category) {
            $structure = explode('/', $category->getPath());
            $pathSize = count($structure);
            if ($pathSize > 2) {
                $path = array();
                $this->_categories[implode('/', $path)] = $category->getId();
                for ($i = 1; $i < $pathSize; $i++) {
                    $item = $collection->getItemById($structure[$i]);
                    if ($item instanceof Varien_Object) {
                        $path[] = $item->getName();
                    }
                }

                // additional options for category referencing: name starting from base category, or category id
                $this->_categories[implode('/', $path)] = $category->getId();
                array_shift($path);
                $this->_categories[implode('/', $path)] = $category->getId();
                $this->_categories[$category->getId()] = $category->getId();
            }
        }
    }
}