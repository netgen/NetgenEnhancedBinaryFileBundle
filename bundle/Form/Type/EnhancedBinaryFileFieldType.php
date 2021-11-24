<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Form\Type;

use EzSystems\EzPlatformContentForms\ConfigResolver\MaxUploadSize;
use EzSystems\EzPlatformContentForms\Form\Type\FieldType\BinaryBaseFieldType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EnhancedBinaryFileFieldType extends \Symfony\Component\Form\AbstractType
{
    /** @var MaxUploadSize */
    private $maxUploadSize;

    public function __construct(MaxUploadSize $maxUploadSize)
    {
        $this->maxUploadSize = $maxUploadSize;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_enhancedbinaryfile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'ezplatform_content_forms_fieldtype',
                'mime_types' => [],
                'mime_types_message' => null
            ]
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraints = ['maxSize' => $this->maxUploadSize->get()];

        if ($options['mime_types'])
        {
            $constraints['mimeTypes'] = $options['mime_types'];
            if ($options['mime_types_message'])
            {
                $constraints['mimeTypesMessage'] = $options['mime_types_message'];
            }
        }

        $builder
            ->add(
                'file',
                FileType::class,
                [
                    'label' => false,
                    'required' => $options['required'],
                    'constraints' => [
                        new Assert\File(
                            $constraints
                        ),
                    ],
                ]
            );
    }
}