<?php

namespace Shopware\Product\Gateway;

use Shopware\Context\TranslationContext;
use Shopware\Product\Exception\NotSupportedFetchMode;
use Shopware\Product\Struct\ProductCollection;
use Shopware\Search\Criteria;
use Shopware\Search\SearchResult;

class ProductRepository
{
    const FETCH_MINIMAL = 'minimal';

    /**
     * @var ProductReader
     */
    private $reader;

    /**
     * @var ProductSearcher
     */
    private $searcher;

    public function __construct(ProductReader $reader, ProductSearcher $searcher)
    {
        $this->reader = $reader;
        $this->searcher = $searcher;
    }

    public function search(Criteria $criteria, TranslationContext $context): SearchResult
    {
        return $this->searcher->search($criteria, $context);
    }

    public function read(array $numbers, TranslationContext $context, string $fetchMode): ProductCollection
    {
        switch ($fetchMode) {
            case self::FETCH_MINIMAL:
                return $this->reader->read($numbers, $context);

            default:
                throw new NotSupportedFetchMode($fetchMode);
        }
    }
}