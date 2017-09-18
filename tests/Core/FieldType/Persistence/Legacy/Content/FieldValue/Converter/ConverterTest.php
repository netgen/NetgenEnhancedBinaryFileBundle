<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\Core\FieldType\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    public function setUp()
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->converter = new Converter($this->configResolver);
    }

    public function testInstanceOfConverterInterface()
    {
        $this->assertInstanceOf(\eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter::class, $this->converter);
    }

    public function testToStorageValueShouldDoNothing()
    {
        $this->converter->toStorageValue(
            $this->createMock(FieldValue::class),
            $this->createMock(StorageFieldValue::class)
        );
    }

    public function testToFieldValueShouldDoNothing()
    {
        $this->converter->toFieldValue(
            $this->createMock(StorageFieldValue::class),
            $this->createMock(FieldValue::class)
        );
    }

    public function testGetIndexColumnShouldReturnFalse()
    {
        $this->assertFalse($this->converter->getIndexColumn());
    }

    public function testToStorageFieldDefinitionWithoutConstraints()
    {
        $fieldDefinition = new FieldDefinition();
        $storage = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition($fieldDefinition, $storage);

        $this->assertEquals(0, $storage->dataInt1);
        $this->assertEquals('', $storage->dataText1);
    }

    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new FieldDefinition();
        $fieldDefinition->fieldTypeConstraints->validators = array(
            'FileSizeValidator' => array(
                'maxFileSize' => 14,
            ),
        );
        $fieldDefinition->fieldTypeConstraints->fieldSettings = array(
            'allowedTypes' => array(
                'text/plain',
            ),
        );
        $storage = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition($fieldDefinition, $storage);

        $this->assertEquals(14, $storage->dataInt1);
        $this->assertEquals(array('text/plain'), $storage->dataText1);
    }

    public function testToFieldDefinition()
    {
        $fieldDefinition = new FieldDefinition();
        $storage = new StorageFieldDefinition();

        $this->converter->toFieldDefinition($storage, $fieldDefinition);

        $this->assertInstanceOf(FieldTypeConstraints::class, $fieldDefinition->fieldTypeConstraints);
        $this->assertEquals(false, $fieldDefinition->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize']);
        $this->assertEquals('', $fieldDefinition->fieldTypeConstraints->fieldSettings['allowedTypes']);
    }

    public function testToFieldDefinitionWithValidator()
    {
        $fieldDefinition = new FieldDefinition();
        $storage = new StorageFieldDefinition();
        $storage->dataInt1 = 55;
        $storage->dataText1 = 'text/plain';

        $this->converter->toFieldDefinition($storage, $fieldDefinition);

        $this->assertInstanceOf(FieldTypeConstraints::class, $fieldDefinition->fieldTypeConstraints);
        $this->assertEquals(55, $fieldDefinition->fieldTypeConstraints->validators['FileSizeValidator']['maxFileSize']);
        $this->assertEquals('text/plain', $fieldDefinition->fieldTypeConstraints->fieldSettings['allowedTypes']);
    }
}
