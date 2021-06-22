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

namespace Functional\Bundle\SearchBundleDBAL\SearchTerm;

use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchTermQueryBuilder;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class SearchTermQueryBuilderTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const SEARCH_TERM = 'sasse tea';

    public function testRankingQueryDoesNotConsiderInactiveProducts(): void
    {
        $searchTermQueryBuilder = Shopware()->Container()->get(SearchTermQueryBuilder::class);
        $modelManager = Shopware()->Container()->get('models');
        $productRepository = $modelManager->getRepository(Article::class);

        $query = $searchTermQueryBuilder->buildQuery(self::SEARCH_TERM);

        if (!($query instanceof QueryBuilder)) {
            static::fail(sprintf('Instance of %s expected.', QueryBuilder::class));
        }

        $stmt = $query->execute();

        if (!($stmt instanceof \PDOStatement)) {
            static::fail(sprintf('Instance of %s expected.', \PDOStatement::class));
        }

        $ranking = $stmt->fetchAll();

        $bestMatch = array_shift($ranking);
        $product = $productRepository->find($bestMatch['product_id']);

        if (!($product instanceof Article)) {
            static::fail(sprintf('Instance of %s expected.', Article::class));
        }

        $product->setActive(false);
        $modelManager->flush($product);

        $stmt = $query->execute();

        if (!($stmt instanceof \PDOStatement)) {
            static::fail(sprintf('Instance of %s expected.', \PDOStatement::class));
        }

        $ranking = $stmt->fetchAll();

        $bestMatchNew = array_shift($ranking);

        static::assertNotEquals($bestMatch, $bestMatchNew);
    }
}
