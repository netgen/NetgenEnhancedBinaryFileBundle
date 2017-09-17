<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\Form\FieldTypeHandler;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value;
use Netgen\Bundle\EnhancedBinaryFileBundle\Form\FieldTypeHandler\EnhancedFile;
use Netgen\Bundle\EzFormsBundle\Form\FieldTypeHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EnhancedFileTest extends TestCase
{
    /**
     * @var EnhancedFile
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolver;

    public function setUp()
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->handler = new EnhancedFile($this->configResolver);
    }

    public function testInstanceOfFieldTypeHandler()
    {
        $this->assertInstanceOf(FieldTypeHandler::class, $this->handler);
    }

    public function testConvertFieldValueToFormShouldDoNothing()
    {
        $this->handler->convertFieldValueToForm(new Value(), null);
    }

    public function testConvertFieldValueFromFormWithNull()
    {
        $this->assertNull($this->handler->convertFieldValueFromForm(null));
    }

    public function testConvertFieldValueFromFormWithFile()
    {
        $file = new UploadedFile(__DIR__ . '/test.txt', 'test.txt');
        $file->getClientMimeType();
        $result = $this->handler->convertFieldValueFromForm($file);

        $this->assertTrue(is_array($result));
        $this->assertEquals($file->getFileInfo()->getRealPath(), $result['inputUri']);
        $this->assertEquals($file->getClientOriginalName(), $result['fileName']);
        $this->assertEquals($file->getSize(), $result['fileSize']);
        $this->assertEquals($file->getClientMimeType(), $result['mimeType']);
    }

    public function testBuildFieldCreateForm()
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $fieldDefinition = new FieldDefinition(array(
            'fieldSettings' => array(
                'allowedTypes' => 'jpg|pdf|txt',
            ),
        ));
        $lang = 'eng_US';

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
            ->willReturn(array('text/plain'));

        $this->handler->buildFieldCreateForm($formBuilder, $fieldDefinition, $lang);
    }
}
