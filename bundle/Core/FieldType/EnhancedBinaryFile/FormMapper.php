<?php
/**
 *
 * Author: René Hrdina, styleflasher GmbH
 * Date: 29.11.18
 * Time: 14:16
 */

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
            );
    }
}