<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\FieldHandler;

use eZ\Publish\Core\FieldType\Integer\Value as IntValue;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value;
use Netgen\Bundle\EnhancedBinaryFileBundle\FieldHandler\EnhancedBinaryFileHandler;
use Netgen\Bundle\InformationCollectionBundle\FieldHandler\Custom\CustomLegacyFieldHandlerInterface;
use PHPUnit\Framework\TestCase;

class EnhancedBinaryFileHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var EnhancedBinaryFileHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->io = $this->createMock(IOServiceInterface::class);
        $this->handler = new EnhancedBinaryFileHandler($this->io);
    }

    public function testInstanceOfCustomLegacyFieldHandlerInterface()
    {
        $this->assertInstanceOf(CustomLegacyFieldHandlerInterface::class, $this->handler);
    }

    public function testSupports()
    {
        $this->assertTrue($this->handler->supports(new Value()));
        $this->assertFalse($this->handler->supports(new IntValue(1)));
    }

    public function testToString()
    {
        $file = new Value(
            [
                'path' => __DIR__ . '/test.txt',
            ]
        );

        $this->assertEquals('', $this->handler->toString($file, new FieldDefinition()));
    }

    public function testGetLegacyValue()
    {
        $file = new Value(
            [
                'path' => __DIR__ . '/test.txt',
            ]
        );

        $struct = new BinaryFileCreateStruct();
        $binaryFile = new BinaryFile();

        $this->io->expects($this->once())
            ->method('newBinaryCreateStructFromLocalFile')
            ->with($file->inputUri)
            ->willReturn($struct);

        $this->io->expects($this->once())
            ->method('createBinaryFile')
            ->with($struct)
            ->willReturn($binaryFile);

        $data = $this->handler->getLegacyValue($file, new FieldDefinition(['id' => 123]));
    }
}
