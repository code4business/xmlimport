<?php

/**
 * Test for Product Builder class.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 **/
class C4B_XmlImport_Test_Model_Source_ProductBuilder extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var C4B_XmlImport_Test_DataProviders_ProductBuilder
     */
    protected $_dataProvider;

    /**
     * @var C4B_XmlImport_Test_MockProvider
     */
    protected $_mockProvider;


    /**
     * Prepare data and mock providers
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_dataProvider = new C4B_XmlImport_Test_DataProviders_ProductBuilder();
        $this->_mockProvider = new C4B_XmlImport_Test_MockProvider();
    }

    /**
     * @test
     * @loadExpectation simple
     */
    public function test_returnsProductData()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getMinimal() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     * @loadExpectation simple_german_french
     * @loadFixture stores_german_french
     */
    public function test_returnsProductDataStores()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getMinimalTranslatedDeFr() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     * @loadExpectation simple_german
     * @loadFixture stores_german
     */
    public function test_missingStoreDataIgnored()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getMinimalTranslatedDeFr() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(1, $errors);
        $this->assertContains('does not exist in the system', $errors[0]);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     * @loadExpectation german
     * @loadFixture stores_german
     */
    public function test_storeScopeOnly()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getStoreScopeOnly() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     * @loadExpectation simple_german_french
     * @loadFixture stores_german_french
     */
    public function test_skuAlwaysInDefaultScope()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getSkuInEachScope() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);

        $productData = $productBuilder->getProductData( $this->_dataProvider->getSkuInStoreScope() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     */
    public function test_missingSimpleDataShowsError()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getMissingSimpleData() );

        $this->assertNull($productData);
        $errors = $productBuilder->getErrors();
        $this->assertCount(1, $errors);
        $this->assertContains('Missing <simple_data> node.', $errors[0]);
    }

    /**
     * @test
     * @loadFixture stores_german_french
     * @loadExpectation simple_complex
     */
    public function test_returnsSimpleAndComplexData()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);
        $this->_mockProvider->setComplexAttributeMockReturnsGiven(
          $this,
          $this->expected('mocked-complex-attr-data')->getData()
        );
        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getSimpleComplex() );

        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $this->assertEquals($this->expected('data')->getData(), $productData);
    }

    /**
     * @test
     */
    public function test_complexDataWithMismatchedEnumItems()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setCategoryCreatorMockDummy($this);
        $this->_mockProvider->setComplexAttributeMockReturnsGiven(
            $this,
            null
        );
        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getComplexMismatched() );

        $this->assertNull($productData);
        $errors = $productBuilder->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('Complex attribute at position 1 is invalid.', $errors[0]);
        $this->assertEquals('Invalid complex data.', $errors[1]);
    }

    /**
     * @test
     * @loadExpectation simple_complex
     * @loadFixture stores_german_french
     */
    public function test_missingCategoryIsCreated()
    {
        $this->_mockProvider->setAttributeCreatorMockDummy($this);
        $this->_mockProvider->setComplexAttributeMockReturnsGiven(
            $this,
            $this->expected('mocked-complex-attr-data')->getData()
        );
        $this->_mockProvider->setCategoryCreatorMockReturnsGiven(
            $this,
            $this->expected('mocked-category-creator-data')->getData()
        );

        /** @var C4B_XmlImport_Model_Source_ProductBuilder $productBuilder */
        $productBuilder = Mage::getModel('xmlimport/source_productBuilder');
        $productData = $productBuilder->getProductData( $this->_dataProvider->getSimpleComplex() );

        $this->assertEquals($this->expected('data')->getData(), $productData);
        $errors = $productBuilder->getErrors();
        $this->assertCount(0, $errors);
        $notices = $productBuilder->getNotices();
        $this->assertCount(1, $notices);
        $this->assertEquals($notices[0], 'Created category Subcategory');
    }

    //TODO: CategoryCreator returns false, category is removed
    //TODO: CategoryCreator option to create missing categories
    //TODO: events dispatched
    //TODO: Observer changes data
    //TODO: Observer invalidates data
    //TODO: Observer errors
}