<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\Mapper\BinaryFileFormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class FormMapper extends BinaryFileFormMapper
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        parent::mapFieldDefinitionForm($fieldDefinitionForm, $data);

        $fieldDefinitionForm
            ->add(
                'allowedTypes',
                TextType::class,
                [
                    'required' => false,
                    'property_path' => 'fieldSettings[allowedTypes]',
                    'label' => 'field_definition.enhancedbinaryfile.allowedTypes',
                    'translation_domain' => 'messages',
                ]
            )
            ->add(
                'mimeTypesMessage',
                TextType::class,
                [
                    'required' => false,
                    'property_path' => 'fieldSettings[mimeTypesMessage]',
                    'label' => 'field_definition.enhancedbinaryfile.mimeTypesMessage',
                    'translation_domain' => 'messages',
                ]
            );
    }
}
