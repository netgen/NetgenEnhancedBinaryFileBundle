<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Form\FieldTypeHandler;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\FieldType\Value;
use Netgen\Bundle\EzFormsBundle\Form\FieldTypeHandler;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value as EnhancedFileValue;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints;

/**
 * Class EnhancedFile.
 */
class EnhancedFile extends FieldTypeHandler
{
    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function convertFieldValueToForm(Value $value, FieldDefinition $fieldDefinition = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param UploadedFile $data
     */
    public function convertFieldValueFromForm($data)
    {
        if (null === $data) {
            return null;
        }

        return new EnhancedFileValue(
            [
                'path' => $data->getFileInfo()->getRealPath(),
                'fileName' => $data->getClientOriginalName(),
                'fileSize' => $data->getSize(),
                'mimeType' => $data->getClientMimeType(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFieldForm(
        FormBuilderInterface $formBuilder,
        FieldDefinition $fieldDefinition,
        $languageCode,
        Content $content = null
    ) {
        $options = $this->getDefaultFieldOptions($fieldDefinition, $languageCode, $content);

        $maxFileSize = $fieldDefinition->validatorConfiguration['FileSizeValidator']['maxFileSize'];
        $allowedExtensions = $fieldDefinition->fieldSettings['allowedTypes'];

        if (null !== $maxFileSize || !empty($allowedExtensions)) {
            $constraints = array();

            if (null !== $maxFileSize && !empty($maxFileSize)) {
                $constraints['maxSize'] = strval($maxFileSize) . "M";
            }

            if (!empty($allowedExtensions)) {
                $allowedExtensions = explode('|', $allowedExtensions);

                $allowedMimeTypes = array();

                foreach ($allowedExtensions as $allowedExtension) {
                    if ($this->configResolver->hasParameter("{$allowedExtension}.Types", 'mime')) {
                        $allowedMimeTypes = array_merge($allowedMimeTypes, $this->configResolver->getParameter("{$allowedExtension}.Types", 'mime'));
                    }
                }
                $constraints['mimeTypes'] = $allowedMimeTypes;
            }

            $options['constraints'][] = new Constraints\File($constraints);
        }

        // EnhancedBinaryFile should not be erased (updated as empty) if nothing is selected in file input
        $this->skipEmptyUpdate($formBuilder, $fieldDefinition->identifier);

        $formBuilder->add($fieldDefinition->identifier, FileType::class, $options);
    }
}
