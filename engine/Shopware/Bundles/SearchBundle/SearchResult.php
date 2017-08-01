<?php

namespace SearchBundle;

class SearchResult
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
}