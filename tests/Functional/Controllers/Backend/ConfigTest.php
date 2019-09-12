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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Shopware\Models\Shop\Locale;

class ConfigTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Tests the cron job config pagination
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
     * Tests the cron job search
     */
    public function testCronJobSearchConfig()
    {
        $sql = 'SELECT count(*) FROM  s_crontab';
        $totalCronJobCount = Shopware()->Db()->fetchOne($sql);

        // Test the search
        $this->checkGetTableListSearch('a', $totalCronJobCount, 'cronJob');

        $this->reset();

        // Test the search with a pagination
        $this->checkGetTableListSearchWithPagination('a', 'cronJob');
    }

    /**
     * Tests the searchField config pagination
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
     * Tests the cron job search
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
     * Tests the existence of the document type key
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

        static::assertEquals(true, json_decode($response->getBody(), true)['success']);

        Shopware()->Db()->query('DELETE FROM `s_core_documents` WHERE `key`="first_test_document";');
    }

    /**
     * Tests the document type key unique constraint
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

        static::assertEquals(true, json_decode($response->getBody(), true)['success']);

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

        static::assertEquals(false, json_decode($response->getBody(), true)['success']);

        $this->resetContainer();

        Shopware()->Db()->query('DELETE FROM `s_core_documents` WHERE `key`="test_document";');
    }

    /**
     * Tests whether the list of pdf documents includes its translations
     */
    public function testIfPDFDocumentsListIncludesTranslation()
    {
        // Set up
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth(false);
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        // Login
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'username' => 'demo',
            'password' => 'demo',
        ]);
        $this->dispatch('backend/Login/login');

        $getParams = [
            '_repositoryClass' => 'document',
            '_dc' => '1234567890',
            'page' => '1',
            'start' => '0',
            'limit' => '20',
        ];

        $this->reset();

        // Check if German values are still the same
        $this->Request()->setMethod('GET');
        $getString = http_build_query($getParams);
        $response = $this->dispatch('backend/Config/getList?' . $getString);

        $responseJSON = json_decode($response->getBody(), true);
        static::assertEquals(true, $responseJSON['success']);

        foreach ($responseJSON['data'] as $documentType) {
            static::assertEquals($documentType['name'], $documentType['description']);
        }

        $this->reset();
        Shopware()->Container()->reset('translation');

        // Check for English translations
        $user = Shopware()->Container()->get('auth')->getIdentity();
        $user->locale = Shopware()->Models()->getRepository(
            Locale::class
        )->find(2);

        $this->Request()->setMethod('GET');
        $getString = http_build_query($getParams);
        $response = $this->dispatch('backend/Config/getList?' . $getString);

        $responseJSON = json_decode($response->getBody(), true);
        static::assertEquals(true, $responseJSON['success']);

        foreach ($responseJSON['data'] as $documentType) {
            switch ($documentType['id']) {
                case 1:
                    static::assertEquals('Invoice', $documentType['description']);
                    break;
                case 2:
                    static::assertEquals('Notice of delivery', $documentType['description']);
                    break;
                case 3:
                    static::assertEquals('Credit', $documentType['description']);
                    break;
                case 4:
                    static::assertEquals('Cancellation', $documentType['description']);
                    break;
            }
        }
    }

    /**
     * Tests the config tableList
     *
     * @param string $tableListName
     */
    private function checkTableListConfig($tableListName)
    {
        // Should return more than 2 items
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/Config/getTableList/_repositoryClass/' . $tableListName);
        $returnData = $this->View()->getAssign('data');
        static::assertGreaterThan(2, count($returnData));
        static::assertTrue($this->View()->getAssign('success'));
    }

    /**
     * Tests the config table list with pagination
     *
     * @param strin $tableListName
     */
    private function checkGetTableListConfigPagination($tableListName)
    {
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/Config/getTableList/_repositoryClass/' . $tableListName . '?page=1&start=0&limit=2');
        static::assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');
        static::assertGreaterThan(2, $this->View()->getAssign('total'));
        static::assertCount(2, $returnData);
    }

    /**
     * Checks the search of the table list config
     *
     * @param string $searchTerm
     * @param int    $totalCount
     * @param string $tableListName
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
        static::assertGreaterThan(0, count($returnData));
        static::assertLessThan($totalCount, count($returnData));
        static::assertTrue($this->View()->getAssign('success'));
    }

    /**
     * Checks the search and the pagination of the table list config
     *
     * @param string $searchTerm
     * @param string $tableListName
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
        static::assertCount(2, $returnData);
        static::assertTrue($this->View()->getAssign('success'));
    }

    /**
     * Resets the Shopware container
     */
    private function resetContainer()
    {
        // Synthetic services
        $kernel = Shopware()->Container()->get('kernel');
        $connection = Shopware()->Container()->get('db_connection');
        $application = Shopware()->Container()->get('application');

        Shopware()->Container()->reset();

        Shopware()->Container()->set('kernel', $kernel);
        Shopware()->Container()->set('db_connection', $connection);
        Shopware()->Container()->set('application', $application);
    }
}
