<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class FieldCollection
{
    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @param Field[] ...$fields
     */
    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return Field[]
     */
    public function getFields(string $classFilter): array
    {
        return array_values(array_filter($this->fields, function(Field $field) use ($classFilter) {
            return $field instanceof $classFilter;
        }));
    }

    public function getField(string $classFilter): Field
    {
        $fields = $this->getFields($classFilter);

        if(count($fields) !== 1) {
            throw new \RuntimeException(sprintf('Unable to find field %s', $classFilter));
        }

        return $fields[0];
    }

    /**
     * @return string[]
     */
    public function getFieldNames(string $classFilter): array
    {
        return array_map(function (Field $field) {
            return $field->getName();
        }, $this->getFields($classFilter));
    }

    public function getFieldClasses(): array
    {
        return array_map(function (Field $field) {
            return get_class($field);
        }, $this->fields);

    }

}