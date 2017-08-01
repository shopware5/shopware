<?php

namespace Shopware\Category\Gateway;

use Shopware\Category\Struct\CategoryCollection;
use Shopware\Context\TranslationContext;
use Shopware\Search\Criteria;
use Shopware\Search\SearchResult;

class CategoryRepository
{
    const FETCH_LIST = 'list';

    /**
     * @var CategoryReader
     */
    private $reader;

    /**
     * @var CategorySearcher
     */
    private $searcher;

    public function __construct(CategoryReader $reader, CategorySearcher $searcher)
    {
        $this->reader = $reader;
        $this->searcher = $searcher;
    }

    public function search(Criteria $criteria, TranslationContext $context): SearchResult
    {
        return $this->searcher->search($criteria, $context);
    }

    public function read(array $ids, TranslationContext $context, string $fetchMode): CategoryCollection
    {
        return $this->reader->read($ids, $context);
    }
}