<?php

declare(strict_types=1);

namespace Wowstack\Dbc;

use Symfony\Component\Yaml\Yaml;
use Wowstack\Dbc\MappingField as Mappings;
use Wowstack\Dbc\MappingField\MappingFieldInterface;
use Wowstack\Dbc\MappingField\MappingException;

class Mapping
{
    /**
     * @var MappingFieldInterface[]
     */
    protected $_fields = [];

    /**
     * @var int
     */
    protected $_fieldCount = 0;

    /**
     * @var int
     */
    protected $_fieldSize = 0;

    /**
     * @var bool
     */
    protected $_hasStrings = false;

    /**
     * Create an instance.
     *
     * @param [] $mapping
     */
    public function __construct(array $mapping = [])
    {
        $fields = isset($mapping['fields']) ? $mapping['fields'] : [];

        foreach ($fields as $field_name => $field_parameters) {
            $this->add($field_name, $field_parameters);
            $this->_fieldCount += $this->_fields[$field_name]->getTotalCount();
            $this->_fieldSize += $this->_fields[$field_name]->getTotalSize();

            if ('string' === $this->_fields[$field_name]->getType() ||
            'localized_string' === $this->_fields[$field_name]->getType()) {
                $this->_hasStrings = true;
            }
        }
    }

    /**
     * Adds a field type to the mapping list.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @throws MappingException
     */
    public function add(string $name, array $parameters)
    {
        if (!isset($parameters['type'])) {
            throw new MappingException('Field definition is missing a type.');
        }

        switch ($parameters['type']) {
            case 'float':
                $field = new Mappings\FloatField($name, $parameters);
                break;
            case 'localized_string':
                $field = new Mappings\LocalizedStringField($name, $parameters);
                break;
            case 'char':
                $field = new Mappings\SignedCharField($name, $parameters);
                break;
            case 'int':
                $field = new Mappings\SignedIntegerField($name, $parameters);
                break;
            case 'string':
                $field = new Mappings\StringField($name, $parameters);
                break;
            case 'uchar':
                $field = new Mappings\UnsignedCharField($name, $parameters);
                break;
            case 'uint':
                $field = new Mappings\UnsignedIntegerField($name, $parameters);
                break;
            case 'foreign_key':
                $field = new Mappings\ForeignKeyField($name, $parameters);
                break;
            default:
                throw new MappingException('Unknown field type specified');
        }

        $this->_fields[$name] = $field;
    }

    /**
     * Returns the amount of fields in the mapping.
     *
     * @return int
     */
    public function getFieldCount(): int
    {
        return $this->_fieldCount;
    }

    /**
     * Returns the actual amount of columns in the mapping.
     *
     * @return int
     */
    public function getFieldSize(): int
    {
        return $this->_fieldSize;
    }

    /**
     * Returns the mapping type for a field.
     *
     * @param string $name
     *
     * @return string
     */
    public function getFieldType(string $name): string
    {
        return $this->_fields[$name]->getType();
    }

    /**
     * Create an instance with a mapping from file.
     *
     * @param string $yaml path to YAML file
     *
     * @return Mapping
     */
    public static function fromYAML(string $yaml): Mapping
    {
        return new self(Yaml::parseFile($yaml));
    }

    /**
     * Returns if a string field is part of the mapping.
     *
     * @return bool
     */
    public function hasStrings(): bool
    {
        return $this->_hasStrings;
    }

    /**
     * Returns a list of field names.
     *
     * @return array
     */
    public function getFieldNames(): array
    {
        $field_names = [];

        foreach ($this->_fields as $field) {
            $field_list = $field->getParsedFields();
            foreach ($field_list as $field_name => $field_data) {
                $field_names[] = $field_name;
            }
        }

        return $field_names;
    }

    /**
     * Returns the resulting parsed field data.
     *
     * @var array
     */
    public function getParsedFields(): array
    {
        $parsed_fields = [];

        foreach ($this->_fields as $field) {
            $field_list = $field->getParsedFields();
            foreach ($field_list as $field_name => $field_data) {
                $parsed_fields[$field_name] = $field_data;
            }
        }

        return $parsed_fields;
    }
}
