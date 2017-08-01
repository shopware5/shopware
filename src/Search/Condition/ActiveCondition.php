<?php

namespace Shopware\Search\Condition;

use Shopware\Search\ConditionInterface;

class ActiveCondition implements ConditionInterface
{
    /**
     * @var boolean
     */
    protected $active;

    public function __construct(bool $active = true)
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getName(): string
    {
        return self::class;
    }
}