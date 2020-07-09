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

namespace Shopware\Tests\Functional\Bundle\SearchBundleDBAL;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class SearchIndexerTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * Tests building the search index.
     * Example data is loaded and search index is populated with these values (see data.sql).
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testSearchIndexer(): void
    {
        $connection = Shopware()->Models()->getConnection();

        // Load example content
        $connection->exec(file_get_contents(__DIR__ . '/fixtures/data.sql'));

        // Build search index with populated values
        Shopware()->Container()->get('shopware_searchdbal.search_indexer')->build();

        // If keyword/index cleanup wouldn't work, we'd have 10 keywords indexed overall:
        // - testarticle (10 matches) / examplearticle (1 match)
        // - testcategory (10 matches) / examplecategory (1 match)
        // - testsupplier (10 matches) / examplesupplier (1 match)
        // - testordernumber (10 matches) / exampleordernumber (1 match)
        // - foo (5 matches) / bar (6 matches)

        // As keywords matching on more than 90% of the referenced table should be deleted, all keywords beginning with "test" should've disappeared.
        // "foo" and "bar" should both be retained, as they match on less than 90% of translations.

        $keywords = array_column($connection->fetchAll('SELECT * FROM s_search_keywords'), 'keyword');
        $index = $connection->fetchAll('SELECT * FROM s_search_index');

        static::assertEquals(['examplearticle', 'examplecategory', 'examplesupplier', 'exampleordernumber', 'foo', 'bar'], $keywords);

        // The 4 "example..." keywords + all "foo" (5) and "bar" (6) keywords
        static::assertCount(15, $index);
    }
}
