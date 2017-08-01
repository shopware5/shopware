<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class FieldCollection
{
    /**
     * @var Field[]
     */
    private $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

}