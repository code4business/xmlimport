<?php

/**
 * Test for category creator class
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 **/
class C4B_XmlImport_Test_Model_Source_ProductBuilder_CategoryCreator extends EcomDev_PHPUnit_Test_Case
{
    public function setUp()
    {
        $categoryMock = $this->getModelMock('catalog/category', array('save'));
        $categoryMock->expects($this->any())->method('save')->willReturnSelf();
        $categoryMock->expects($this->any())->method('getId')->willReturn(55);
        $this->replaceByMock('model', 'catalog/category', $categoryMock);
    }

    /**
     * @test
     */
    public function test_nonExistingCategoryIsCreated()
    {
        /** @var C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator $categoryCreator */
        $categoryCreator = Mage::getModel('xmlimport/source_productBuilder_categoryCreator');
        $this->assertTrue( $categoryCreator->createIfItNotExists('Nonexisting') );
        $messages = $categoryCreator->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals('notice', $messages[0]['type']);
    }

    /**
     * @test
     */
    public function test_categoryCreatedEventIsDispatched()
    {
        /** @var C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator $categoryCreator */
        $categoryCreator = Mage::getModel('xmlimport/source_productBuilder_categoryCreator');
        $this->assertTrue( $categoryCreator->createIfItNotExists('Nonexisting') );
        $this->assertEventDispatched(C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator::EVENT_MISSING_CATEGORY_CREATED);
    }

    /**
     * @test
     */
    public function test_categoryNameHasEmptyPath()
    {
        /** @var C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator $categoryCreator */
        $categoryCreator = Mage::getModel('xmlimport/source_productBuilder_categoryCreator');
        $this->assertFalse( $categoryCreator->createIfItNotExists('Nonexisting//empty_path') );
        $messages = $categoryCreator->getMessages();
        $this->assertCount(2, $messages);
        $this->assertEquals('error', $messages[1]['type']);
        $this->assertContains('empty path parts', $messages[1]['message']);
    }

    /**
     * @test
     */
    public function test_categorySaveThrowsException()
    {
        $exception = new Mage_Core_Exception('Test Exception');
        $categoryMock = $this->getModelMock('catalog/category', array('save'));
        $categoryMock->expects($this->any())->method('save')->willThrowException($exception);
        $this->replaceByMock('model', 'catalog/category', $categoryMock);

        /** @var C4B_XmlImport_Model_Source_ProductBuilder_CategoryCreator $categoryCreator */
        $categoryCreator = Mage::getModel('xmlimport/source_productBuilder_categoryCreator');
        $this->assertFalse( $categoryCreator->createIfItNotExists('Exception') );
        $messages = $categoryCreator->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals('error', $messages[0]['type']);
        $this->assertContains('can not be saved', $messages[0]['message']);
    }
}