<?php

namespace Shopware\Search;

class SearchResult implements \IteratorAggregate
{
    /**
     * @var array[]
     */
    public $rows;

    /**
     * @var int
     */
    public $total;

    public function __construct(array $rows, $total)
    {
        $this->rows = $rows;
        $this->total = $total;
    }

    public function fetchColumn(string $column): array
    {
        return array_column($this->rows, $column);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->rows);
    }
}