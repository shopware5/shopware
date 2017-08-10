<?php

namespace Shopware\Search\Condition;

use Shopware\Search\ConditionInterface;

class NameCondition implements ConditionInterface
{
    /**
     * @var string[]
     */
    protected $names = [];

    public function __construct(array $names)
    {
        $this->names = $names;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getNames(): array
    {
        return $this->names;
    }
}