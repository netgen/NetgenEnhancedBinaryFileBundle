<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\FieldType\DataTransformer\BinaryFileValueTransformer;
use EzSystems\EzPlatformContentForms\FieldType\Mapper\BinaryFileFormMapper;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\DataTransformer\EnhancedBinaryFileValueTransformer;
use Netgen\Bundle\EnhancedBinaryFileBundle\Form\Type\EnhancedBinaryFileFieldType;
use Symfony\Component\Form\FormInterface;
use Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile\Value as FieldValue;

class FieldValueFormMapper extends BinaryFileFormMapper
{
    /** @var FieldTypeService */
    private $fieldTypeService;

    private $configResolver;

    public function __construct(FieldTypeService $fieldTypeService, ConfigResolverInterface $configResolver)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->configResolver = $configResolver;
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $fieldType = $this->fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);
        $allowedFileExtensions = $fieldDefinition->fieldSettings['allowedTypes'] ?? [];
        $mimeTypesMessage = $fieldDefinition->fieldSettings['mimeTypesMessage'] ?? null;

        $allowedMimeTypes = [];
        if (!empty($allowedFileExtensions)) {
            $allowedExtensions = explode('|', $allowedFileExtensions);

            foreach ($allowedExtensions as $allowedExtension) {
                if ($this->configResolver->hasParameter("{$allowedExtension}.Types", 'mime')) {
                    $allowedMimeTypes = array_merge($allowedMimeTypes, $this->configResolver->getParameter("{$allowedExtension}.Types", 'mime'));
                }
            }

        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        EnhancedBinaryFileFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                            'mime_types' => array_unique($allowedMimeTypes),
                            'mime_types_message' => $mimeTypesMessage
                        ]
                    )
                    ->addModelTransformer(new EnhancedBinaryFileValueTransformer($fieldType, $data->value, FieldValue::class))
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}