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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Backend_ConfigTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * tests the cron job config pagination
     */
    public function testCronJobPaginationConfig()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->checkTableListConfig('cronJob');

        $this->reset();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->checkGetTableListConfigPagination('cronJob');
    }

    /**
     * tests the cron job search
     */
    public function testCronJobSearchConfig()
    {
        $sql = 'SELECT count(*) FROM  s_crontab';
        $totalCronJobCount = Shopware()->Db()->fetchOne($sql);

        //test the search
        $this->checkGetTableListSearch('a', $totalCronJobCount, 'cronJob');

        $this->reset();

        //test the search with a pagination
        $this->checkGetTableListSearchWithPagination('a', 'cronJob');
    }

    /**
     * tests the searchField config pagination
     */
    public function testSearchFieldConfig()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->checkTableListConfig('searchField');

        $this->reset();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->checkGetTableListConfigPagination('searchField');
    }

    /**
     * tests the cron job search
     */
    public function testSearchFieldSearchConfig()
    {
        $sql = 'SELECT count(*)
                FROM s_search_fields f
                LEFT JOIN s_search_tables t on f.tableID = t.id';
        $totalCronJobCount = Shopware()->Db()->fetchOne($sql);

        $this->checkGetTableListSearch('b', $totalCronJobCount, 'searchField');

        $this->reset();

        $this->checkGetTableListSearchWithPagination('b', 'searchField');
    }

    /**
     * tests the existence of the document type key
     */
    public function testPersistDocumentTypeKey()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();

        $newTestDocumentType = [
            'id' => 0,
            'name' => 'Test document 1',
            'key' => 'first_test_document',
            'template' => 'index.tpl',
            'numbers' => 'user',
            'left' => 2,
            'right' => 3,
            'top' => 4,
            'bottom' => 5,
            'pageBreak' => 6,
            'elements' => [],
        ];

        $this->Request()->setPost($newTestDocumentType);
        $response = $this->dispatch('backend/Config/saveValues?_repositoryClass=document');

        $this->assertEquals(true, json_decode($response->getBody(), true)['success']);

        Shopware()->Db()->query('DELETE FROM `s_core_documents` WHERE `id` > 4;');
    }

    /**
     * tests the document type key unique constraint
     */
    public function testDocumentTypeKeyUniqueConstraint()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();

        $firstTestDocumentType = [
            'id' => 0,
            'name' => 'Test document 1',
            'key' => 'test_document',
            'template' => 'index.tpl',
            'numbers' => 'user',
            'left' => 2,
            'right' => 3,
            'top' => 4,
            'bottom' => 5,
            'pageBreak' => 6,
            'elements' => [],
        ];

        $this->Request()->setPost($firstTestDocumentType);
        $response = $this->dispatch('backend/Config/saveValues?_repositoryClass=document');

        $this->assertEquals(true, json_decode($response->getBody(), true)['success']);

        // Try to add another document type with the same document type
        $secondTestDocumentType = [
            'id' => 0,
            'name' => 'Test document 2',
            'key' => 'test_document',
            'template' => 'index.tpl',
            'numbers' => 'user',
            'left' => 2,
            'right' => 3,
            'top' => 4,
            'bottom' => 5,
            'pageBreak' => 6,
            'elements' => [],
        ];

        $this->Request()->setPost($secondTestDocumentType);
        $response = $this->dispatch('backend/Config/saveValues?_repositoryClass=document');

        $this->assertEquals(false, json_decode($response->getBody(), true)['success']);

        Shopware()->Db()->query('DELETE FROM `s_core_documents` WHERE `id` > 4;');
    }

    /**
     * test the config tableList
     *
     * @param $tableListName
     */
    private function checkTableListConfig($tableListName)
    {
        // should return more than 2 items
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/Config/getTableList/_repositoryClass/' . $tableListName);
        $returnData = $this->View()->getAssign('data');
        $this->assertGreaterThan(2, count($returnData));
        $this->assertTrue($this->View()->getAssign('success'));
    }

    /**
     * test the config table list with pagination
     *
     * @param $tableListName
     */
    private function checkGetTableListConfigPagination($tableListName)
    {
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/Config/getTableList/_repositoryClass/' . $tableListName . '?page=1&start=0&limit=2');
        $this->assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');
        $this->assertGreaterThan(2, $this->View()->getAssign('total'));
        $this->assertCount(2, $returnData);
    }

    /**
     * checks the search of the table list config
     *
     * @param $searchTerm
     * @param $totalCount
     * @param $tableListName
     */
    private function checkGetTableListSearch($searchTerm, $totalCount, $tableListName)
    {
        $queryParams = [
            'page' => '1',
            'start' => '0',
            'limit' => 25,
            'filter' => json_encode(
                [
                    [
                        'property' => 'name',
                        'value' => '%' . $searchTerm . '%',
                    ],
                ]
            ),
        ];
        $query = http_build_query($queryParams);
        $url = 'backend/Config/getTableList/_repositoryClass/' . $tableListName . '?';
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->dispatch($url . $query);
        $returnData = $this->View()->getAssign('data');
        $this->assertGreaterThan(0, count($returnData));
        $this->assertLessThan($totalCount, count($returnData));
        $this->assertTrue($this->View()->getAssign('success'));
    }

    /**
     *  checks the search and the pagination of the table list config
     *
     * @param $searchTerm
     * @param $tableListName
     */
    private function checkGetTableListSearchWithPagination($searchTerm, $tableListName)
    {
        $queryParams = [
            'page' => '1',
            'start' => '0',
            'limit' => 2,
            'filter' => json_encode(
                [
                    [
                        'property' => 'name',
                        'value' => '%' . $searchTerm . '%',
                    ],
                ]
            ),
        ];

        $query = http_build_query($queryParams);
        $url = 'backend/Config/getTableList/_repositoryClass/' . $tableListName . '?';
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->dispatch($url . $query);
        $returnData = $this->View()->getAssign('data');
        $this->assertCount(2, $returnData);
        $this->assertTrue($this->View()->getAssign('success'));
    }
}
