<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\Core\FieldType\EnhancedBinaryFile;

use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Type;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mimeTypeDetector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var string
     */
    protected $file;

    public function setUp()
    {
        $this->file = __DIR__ . '/test.txt';
        $this->mimeTypeDetector = $this->createMock(MimeTypeDetector::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->type = new Type($this->mimeTypeDetector, $this->configResolver);
    }

    public function testGetEmptyValue()
    {
        $this->assertEquals(new Value(), $this->type->getEmptyValue());
    }

    public function testGetFieldTypeIdentifier()
    {
        $this->assertEquals('enhancedezbinaryfile', $this->type->getFieldTypeIdentifier());
    }

    public function testValidate()
    {
        $fieldDefinition = new FieldDefinition([
            'fieldSettings' => [
                'allowedTypes' => 'jpg|pdf|txt',
            ],
        ]);

        $value = new Value([
            'path' => $this->file,
        ]);

        $this->mimeTypeDetector->expects($this->once())
            ->method('getFromPath')
            ->with($this->file)
            ->willReturn('text/plain');

        $expected = [
            new ValidationError(
                'This mimeType is not allowed %mimeType%.',
                'These mimeTypes are not allowed %mimeType%.',
                ['mimeType' => 'text/plain']
            ),
        ];

        $this->assertEquals($expected, $this->type->validate($fieldDefinition, $value));
    }

    public function testValidateWithEmptyValue()
    {
        $fieldDefinition = new FieldDefinition([
            'fieldSettings' => [
                'allowedTypes' => 'jpg|pdf|txt',
            ],
        ]);

        $value = new Value();

        $this->type->validate($fieldDefinition, $value);
    }

    public function testValidateWithMineTypesFromConfig()
    {
        $fieldDefinition = new FieldDefinition([
            'fieldSettings' => [
                'allowedTypes' => 'jpg|pdf|txt',
            ],
        ]);

        $value = new Value([
            'path' => $this->file,
        ]);

        $this->mimeTypeDetector->expects($this->once())
            ->method('getFromPath')
            ->with($this->file)
            ->willReturn('text/plain');

        $this->configResolver->expects($this->exactly(3))
            ->method('hasParameter')
            ->will(
                $this->returnCallback(function ($arg) {
                    if ('txt.Types' === $arg) {
                        return true;
                    }

                    return false;
                })
            );

        $this->configResolver->expects($this->once())
            ->method('getParameter')
            ->with('txt.Types', 'mime')
            ->willReturn(['text/plain']);

        $this->type->validate($fieldDefinition, $value);
    }

    public function testValidateWithNoMimeTypesFromConfig()
    {
        $fieldDefinition = new FieldDefinition([
            'fieldSettings' => [
                'allowedTypes' => 'jpg|pdf|txt',
            ],
        ]);

        $value = new Value([
            'path' => $this->file,
        ]);

        $this->mimeTypeDetector->expects($this->once())
            ->method('getFromPath')
            ->with($this->file)
            ->willReturn('text/plain');

        $this->configResolver->expects($this->exactly(3))
            ->method('hasParameter')
            ->will(
                $this->returnCallback(function ($arg) {
                    if ('txt.Types' === $arg) {
                        return true;
                    }

                    return false;
                })
            );

        $this->configResolver->expects($this->once())
            ->method('getParameter')
            ->with('txt.Types', 'mime')
            ->willReturn(['something']);

        $this->type->validate($fieldDefinition, $value);
    }

    public function testValidateFieldSettingsWithEmptyArray()
    {
        $result = $this->type->validateFieldSettings([]);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testValidateFieldSettingsWithBool()
    {
        $result = $this->type->validateFieldSettings(false);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertEquals(new ValidationError('Field settings must be in form of an array'), $result[0]);
    }

    public function testValidateFieldSettingsWithFieldSettings()
    {
        $fieldSettings = [
            'allowedTypes' => [],
            'some_settings' => [],
        ];
        $result = $this->type->validateFieldSettings($fieldSettings);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            new ValidationError(
                "Setting '%setting%' is unknown",
                null,
                ['setting' => 'some_settings']
            ),
            $result[0]
        );
    }

    public function testFromHash()
    {
        $result = $this->type->fromHash([]);

        $this->assertInstanceOf(Value::class, $result);
        $this->assertEquals(new Value(), $result);
    }
}
