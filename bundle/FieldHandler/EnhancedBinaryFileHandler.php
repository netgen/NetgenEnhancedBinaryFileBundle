<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\FieldHandler;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\Value;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value as EnhancedBinaryFileValue;
use eZ\Publish\Core\IO\IOServiceInterface;
use Netgen\Bundle\InformationCollectionBundle\FieldHandler\Custom\CustomLegacyFieldHandlerInterface;
use Netgen\Bundle\InformationCollectionBundle\Value\LegacyData;
use DOMDocument;

class EnhancedBinaryFileHandler implements CustomLegacyFieldHandlerInterface
{
    /**
     * @var IOServiceInterface
     */
    protected $IOService;

    /**
     * EnhancedBinaryFileHandler constructor.
     *
     * @param IOServiceInterface $IOService
     */
    public function __construct(IOServiceInterface $IOService)
    {
        $this->IOService = $IOService;
    }

    /**
     * @inheritDoc
     */
    public function supports(Value $value)
    {
        return $value instanceof EnhancedBinaryFileValue;
    }

    /**
     * @inheritDoc
     */
    public function toString(Value $value, FieldDefinition $fieldDefinition)
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyValue(Value $value, FieldDefinition $fieldDefinition)
    {
        return new LegacyData(
            $fieldDefinition->id,
            0,
            0,
            $this->store($value, $fieldDefinition)
        );
    }

    /**
     * Create XML doc string
     * and save file to filesystem
     *
     * @param EnhancedBinaryFileValue $value
     * @param FieldDefinition $fieldDefinition
     *
     * @return string
     */
    protected function store(EnhancedBinaryFileValue $value, FieldDefinition $fieldDefinition)
    {
        $binaryFile = $this->storeBinaryFileToPath($value);

        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElement( 'binaryfile-info' );
        $binaryFileList = $doc->createElement( 'binaryfile-attributes' );

        $fileInfo = [
            'Filename' => $binaryFile->uri,
            'OriginalFilename' => $value->fileName,
            'Size' => $value->fileSize,
        ];

        foreach($fileInfo as $key => $binaryFileItem) {
            $binaryFileElement = $doc->createElement($key, $binaryFileItem);
            $binaryFileList->appendChild( $binaryFileElement );
        }

        $root->appendChild($binaryFileList);
        $doc->appendChild($root);

        return $doc->saveXML();
    }

    /**
     * Stores file to filesystem
     *
     * @param EnhancedBinaryFileValue $value
     * @param string $storagePrefix
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile
     */
    protected function storeBinaryFileToPath(EnhancedBinaryFileValue $value, $storagePrefix = '/original/collected/')
    {
        $binaryCreateStruct = $this->IOService
            ->newBinaryCreateStructFromLocalFile($value->inputUri);
        $binaryCreateStruct->id = $storagePrefix . $value->fileName;

        $binaryFile = $this->IOService->createBinaryFile($binaryCreateStruct);

        return $binaryFile;
    }
}


