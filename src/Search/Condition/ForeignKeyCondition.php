<?php

namespace Shopware\Search\Condition;

use Shopware\Search\ConditionInterface;

class ForeignKeyCondition implements ConditionInterface
{
    /**
     * @var int[]
     */
    protected $foreignKeys;

    public function __construct(array $foreignKeys)
    {
        $this->foreignKeys = $foreignKeys;
    }

    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    public function getName(): string
    {
        return self::class;
    }
}