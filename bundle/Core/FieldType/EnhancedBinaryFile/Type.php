<?php
/**
 * File containing the BinaryFile Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\IO\MimeTypeDetector;

class Type extends BinaryFileType
{
    /**
     * A settings whitelist used in validateFieldSettings method.
     *
     * @var array
     */
    protected $settingsSchema = array(
        'allowedTypes' => array(
            'type' => 'string',
            'default' => null,
        ),
    );

    /**
     * @var \eZ\Publish\SPI\IO\MimeTypeDetector
     */
    protected $mimeTypeDetector;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(MimeTypeDetector $mimeTypeDetector, ConfigResolverInterface $configResolver)
    {
        $this->mimeTypeDetector = $mimeTypeDetector;
        $this->configResolver = $configResolver;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'enhancedezbinaryfile';
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\BinaryBase\Value $fieldValue The field value for which an action is performed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = array();

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();
        $allowedExtensions = explode('|', $fieldSettings['allowedTypes']);

        $mimeType = $this->mimeTypeDetector->getFromPath($fieldValue->inputUri);

        foreach ($allowedExtensions as $allowedExtension) {
            if ($this->configResolver->hasParameter("{$allowedExtension}.Types", 'mime')) {
                $allowedMimeTypes = $this->configResolver->getParameter("{$allowedExtension}.Types", 'mime');

                if (in_array($mimeType, $allowedMimeTypes, true)) {
                    return parent::validate($fieldDefinition, $fieldValue);
                }
            }
        }

        return array(
            new ValidationError(
                'This mimeType is not allowed %mimeType%.',
                'These mimeTypes are not allowed %mimeType%.',
                array(
                    'mimeType' => $mimeType,
                )
            ),
        );
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $fieldSettings are complete, for this purpose method
     * {@link self::applyDefaultSettings()} is provided.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();

        if (!is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }

        foreach ($fieldSettings as $name => $value) {
            switch ($name) {
                case 'mimeTypesMessage': // break omitted on purpose
                case 'allowedTypes':
                    // Nothing to validate, just recognize this setting as known
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Setting '%setting%' is unknown",
                        null,
                        array(
                            'setting' => $name,
                        )
                    );
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Creates a specific value of the derived class from $inputValue.
     *
     * @param array $inputValue
     *
     * @return Value
     */
    protected function createValue(array $inputValue)
    {
        return new Value($inputValue);
    }
}
