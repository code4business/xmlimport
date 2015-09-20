<?php

use EcomDev_PHPUnit_Test_Case as TestCase;
use EcomDev_PHPUnit_Test_Case_Util as TestUtil;
/**
 * This class handles creation of any and all mock objects.
 *
 * @category    C4B
 * @package     C4B_XmlImport
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author      Dominik Meglič <meglic@code4business.de>
 * @copyright   code4business Software GmbH
 **/
class C4B_XmlImport_Test_MockProvider
{
    /**
     * Set a dummy category creator class that treats any category as if it exists.
     *
     * @param $testCase EcomDev_PHPUnit_Test_Case
     */
    public function setCategoryCreatorMockDummy($testCase)
    {
        $categoryCreatorMock = $testCase->getModelMock(
            'xmlimport/source_productBuilder_categoryCreator', array('createIfItNotExists', 'getErrors', 'getNotices'),
            false, array(), '',
            false
        );
        $categoryCreatorMock->expects($testCase->any())->method('createIfItNotExists')->willReturn(true);
        $categoryCreatorMock->expects($testCase->any())->method('getErrors')->willReturn(array());
        $categoryCreatorMock->expects($testCase->any())->method('getNotices')->willReturn(array());
        TestUtil::replaceByMock('singleton', 'xmlimport/source_productBuilder_categoryCreator', $categoryCreatorMock);
    }

    /**
     * Set a dummy attribute creator class that treats any attribute as if it exists.
     *
     * @param $testCase EcomDev_PHPUnit_Test_Case
     */
    public function setAttributeCreatorMockDummy($testCase)
    {
        $attributeCreatorMock = $testCase->getModelMock(
            'xmlimport/source_productBuilder_attributeCreator', array('createIfNotExists'),
            false, array(), '',
            false
        );
        $attributeCreatorMock->expects($testCase->any())->method('createIfNotExists')->willReturn(true);
        TestUtil::replaceByMock('singleton', 'xmlimport/source_productBuilder_attributeCreator', $attributeCreatorMock);
    }

    /**
     * Complex Attribute Creator that returns given data
     *
     * @param $testCase EcomDev_PHPUnit_Test_Case
     * @param $returnedData array
     */
    public function setComplexAttributeMockReturnsGiven($testCase, $returnedData)
    {
        $complexAttributeMock = $testCase->getModelMock(
            'xmlimport/source_complexAttribute', array('getComplexAttributeData'),
            false, array(), '',
            false
        );
        $complexAttributeMock->expects($testCase->any())->method('getComplexAttributeData')->willReturn(
            $returnedData
        );
        TestUtil::replaceByMock('model', 'xmlimport/source_complexAttribute', $complexAttributeMock);
    }

    /**
     * Category creator that returns given data.
     *
     * @param $testCase EcomDev_PHPUnit_Test_Case
     * @param $returnedData array
     */
    public function setCategoryCreatorMockReturnsGiven($testCase, $returnedData)
    {
        $attributeCreatorMock = $testCase->getModelMock(
            'xmlimport/source_productBuilder_categoryCreator', array('createIfItNotExists', 'getErrors', 'getNotices'),
            false, array(), '',
            false
        );
        $attributeCreatorMock->expects($testCase->any())->method('createIfItNotExists')->will(new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
            array_shift($returnedData)
        ));
        $attributeCreatorMock->expects($testCase->any())->method('getErrors')->will(new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
            array_shift($returnedData)
        ));
        $attributeCreatorMock->expects($testCase->any())->method('getNotices')->will(new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
            array_shift($returnedData)
        ));
        TestUtil::replaceByMock('singleton', 'xmlimport/source_productBuilder_categoryCreator', $attributeCreatorMock);
    }
}