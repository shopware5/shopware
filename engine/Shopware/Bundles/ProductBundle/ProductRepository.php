<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace ProductBundle;

use Shopware\Product\Exception\NotSupportedFetchMode;
use ProductBundle\Gateway\PriceReader;
use ProductBundle\Gateway\ProductReader;
use ProductBundle\Gateway\Searcher\ProductSearcher;
use ProductBundle\Struct\DetailProduct;
use ProductBundle\Struct\ListProduct;
use Shopware\Product\Struct\ProductCollection;
use Shopware\Search\AggregationResult;
use Shopware\Search\SearchResult;
use Shopware\Search\Criteria;
use Shopware\Context\Struct\TranslationContext;

class ProductRepository
{
    const FETCH_DETAIL = 'detail';

    const FETCH_LIST = 'list';

    const FETCH_MINIMAL = 'minimal';

    /**
     * @var ProductReader
     */
    private $productReader;

    /**
     * @var PriceReader
     */
    private $priceReader;

    /**
     * @var ProductSearcher
     */
    private $productSearcher;

    public function __construct(
        ProductReader $productReader,
        PriceReader $priceReader,
        ProductSearcher $productSearcher
    ) {
        $this->productReader = $productReader;
        $this->priceReader = $priceReader;
        $this->productSearcher = $productSearcher;
    }


    public function read(array $numbers, TranslationContext $context, $fetchMode = self::FETCH_MINIMAL): \Shopware\Product\Struct\ProductCollection
    {
        switch ($fetchMode) {
            case self::FETCH_MINIMAL:
                return $this->productReader->read($numbers, $context);

            case self::FETCH_LIST:
                return $this->readList($numbers, $context);

            case self::FETCH_DETAIL:
                return $this->readDetails($numbers, $context);

            default:
                throw new NotSupportedFetchMode($fetchMode);
        }
    }

    public function delete(array $numbers)
    {
        //...
    }

    public function update(array $data)
    {
        //...
    }

    public function search(Criteria $criteria, TranslationContext $context): SearchResult
    {
        return $this->productSearcher->search($criteria, $context);
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        return $this->productSearcher->aggregate($criteria, $context);
    }

    private function readList(array $numbers, TranslationContext $context): \Shopware\Product\Struct\ProductCollection
    {
        $products = $this->productReader->read($numbers, $context);

        $collection = new ProductCollection();
        foreach ($products as $product) {
            $listProduct = ListProduct::createFromProduct($product);

            $collection->add($listProduct);
        }

        $this->eventManager->dispatch(
            new ListProductsLoadedEvent($collection, $context)
        );

        return $collection;
    }

    private function readDetails(array $numbers, TranslationContext $context): \Shopware\Product\Struct\ProductCollection
    {
        $products = $this->readList($numbers, $context);

        $collection = new ProductCollection();
        foreach ($products as $product) {
            $detailProduct = DetailProduct::createFromProduct($product);
            $collection->add($detailProduct);
        }

        $this->eventManager->dispatch(
            new DetailProductsLoadedEvent($collection, $context)
        );

        return $products;
    }
}
