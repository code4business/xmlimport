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
class C4B_XmlImport_Model_CategoryCreator
{
    const EVENT_MISSING_CATEGORY_CREATED = 'c4b_xmlimport_missing_category_created';
    
    const XML_PATH_ROOT_CATEGORY_ID = 'c4b_xmlimport/preprocessing/root_category';
    
    protected $_categories = array();    
    protected $_errors = array();
    
    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->_initCategories();
    }
    
    /**
     * Creates the specified category if it doesn't exist.
     * @param string $categoryName
     * @return C4B_XmlImport_Model_CategoryCreator|int
     */
    public function createIfItNotExists($categoryName)
    {
        if( !isset($this->_categories[$categoryName]) )
        {
            return $this->_createCategoryRecursively($categoryName) != null;
        }
        return true;
    }
    
    /**
     * Retreive an array of messages that were generated during creation of categories and delete.
     * @return array
     */
    public function getErrors()
    {
        $errors = $this->_errors;
        $this->_errors = array();
        return $errors;
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
    
    /**
     * Create nonexistent category and all nonexistent parents recursively.
     * @param string $categoryName Name of category E.g. Kategorien/Keramic/Hersteller5
     * @return int|null
     */
    protected function _createCategoryRecursively($categoryName)
    {
        /* @var $messageHandler C4B_XmlImport_Model_MessageHandler */
        $messageHandler = Mage::getSingleton('xmlimport/messageHandler');
        if (trim($categoryName) == '')
        {
            return null;
        }
    
        $categoryPathArray = explode('/', $categoryName);
    
        // Check that category path does not have empty element names.
        foreach($categoryPathArray as $categoryNameItem)
        {
            if(empty($categoryNameItem))
            {
                $this->_errors[] ="Category [{$categoryName}] can not have empty path parts.";
                return null;
            }
        }
        $newCategoryName = array_pop($categoryPathArray);
        $categoryParentPath = implode('/',$categoryPathArray);

        // If current category's parent category does not exist - create it recursively.
        if( !isset($this->_categories[$categoryParentPath]) )
        {
            if( $this->_createCategoryRecursively($categoryParentPath) == null )
            {
                return null;
            }
        }

        $pathPrefix = '';
        // Recreate category path.
        $categoryParentIds = array();
        foreach($categoryPathArray as $categoryPathItem) //Put check for existence of category id
        {
            if(isset($this->_categories[$pathPrefix . $categoryPathItem]))
            {
                $categoryParentIds[] = $this->_categories[$pathPrefix . $categoryPathItem];
            }else{
                $this->_errors[] = "Category path does not have needed links {$pathPrefix}  {$categoryPathItem}";
            }
            $pathPrefix .= $categoryPathItem . '/';
        }

        if( count($categoryParentIds) > 0 )
        {
            $categoryParentPath = $this->_getCategoryPathPrefix() . '/' . implode('/', $categoryParentIds);
        }else
        {
            $categoryParentPath = $this->_getCategoryPathPrefix();
        }
        // Create a category
        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');
        $category->setName($newCategoryName);
        $category->setIsActive(true);
        $category->setPath($categoryParentPath);
        $category->setDisplayMode( Mage_Catalog_Model_Category::DM_PRODUCT );
        
        Mage::dispatchEvent( self::EVENT_MISSING_CATEGORY_CREATED, array('category' => $category) );

        try {
            $category->save();
            $messageHandler->addNotice("Created category {$categoryName}");
        } catch (Exception $e)
        {
            Mage::logException($e);
            Mage::throwException("Category with name {$newCategoryName} and path {$categoryParentPath} can not be saved.");
        }

        $categoryId = $category->getId();
        $this->_categories[$categoryName] = $categoryId;
        return $categoryId;
    }
    
    /**
     * Get the category path prefix. It consists of the constant 1 and the ID of the root category that is configured.
     * @return string
     */
    protected function _getCategoryPathPrefix()
    {
        return '1/' . Mage::getStoreConfig(self::XML_PATH_ROOT_CATEGORY_ID);
    }
}