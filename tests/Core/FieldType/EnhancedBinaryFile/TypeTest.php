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
        $fieldDefinition = new FieldDefinition(array(
            'fieldSettings' => array(
                'allowedTypes' => 'jpg|pdf|txt',
            ),
        ));

        $value = new Value(array(
            'path' => $this->file,
        ));

        $this->mimeTypeDetector->expects($this->once())
            ->method('getFromPath')
            ->with($this->file)
            ->willReturn('text/plain');

        $expected = array(
            new ValidationError(
                'This mimeType is not allowed %mimeType%.',
                'These mimeTypes are not allowed %mimeType%.',
                array('mimeType' => 'text/plain')
            ),
        );

        $this->assertEquals($expected, $this->type->validate($fieldDefinition, $value));
    }

    public function testValidateWithEmptyValue()
    {
        $fieldDefinition = new FieldDefinition(array(
            'fieldSettings' => array(
                'allowedTypes' => 'jpg|pdf|txt',
            ),
        ));

        $value = new Value();

        $this->type->validate($fieldDefinition, $value);
    }

    public function testValidateWithMineTypesFromConfig()
    {
        $fieldDefinition = new FieldDefinition(array(
            'fieldSettings' => array(
                'allowedTypes' => 'jpg|pdf|txt',
            ),
        ));

        $value = new Value(array(
            'path' => $this->file,
        ));

        $this->mimeTypeDetector->expects($this->once())
            ->method('getFromPath')
            ->with($this->file)
            ->willReturn('text/plain');

        $this->configResolver->expects($this->any())
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
            ->willReturn(array('text/plain'));

        $this->type->validate($fieldDefinition, $value);
    }
}
