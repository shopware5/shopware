<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundleDBAL;

use Enlight_Components_Test_Controller_TestCase;

class SearchIndexerTest extends Enlight_Components_Test_Controller_TestCase
{
    public function setUp()
    {
        Shopware()->Models()->getConnection()->beginTransaction();
        parent::setUp();
    }

    public function tearDown()
    {
        Shopware()->Models()->getConnection()->rollBack();
        parent::tearDown();
    }

    /**
     * Tests building the search index.
     * Example data is loaded and search index is populated with these values (see data.sql).
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testSearchIndexer()
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

        static::assertEquals($keywords, ['examplearticle', 'examplecategory', 'examplesupplier', 'exampleordernumber', 'foo', 'bar']);

        // The 4 "example..." keywords + all "foo" (5) and "bar" (6) keywords
        static::assertCount(15, $index);
    }

}
