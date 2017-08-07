<?php

namespace Shopware\Search\Condition;

use Shopware\Search\ConditionInterface;

class CanonicalCondition implements ConditionInterface
{
    /**
     * @var bool
     */
    protected $isCanonical = true;

    public function __construct(bool $isCanonical = true)
    {
        $this->isCanonical = $isCanonical;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function isCanonical(): bool
    {
        return $this->isCanonical;
    }
}