<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Generator;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Controllers_Backend_Config;

class ConfigTest extends ControllerTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * Tests the cron job config pagination
     */
    public function testCronJobPaginationConfig(): void
    {
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->checkTableListConfig('cronJob');

        $this->reset();

        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->checkGetTableListConfigPagination('cronJob');
    }

    /**
     * Tests the cron job search
     */
    public function testCronJobSearchConfig(): void
    {
        $sql = 'SELECT count(*) FROM s_crontab';
        $totalCronJobCount = (int) $this->getContainer()->get(Connection::class)->fetchOne($sql);

        // Test the search
        $this->checkGetTableListSearch('a', $totalCronJobCount, 'cronJob');

        $this->reset();

        // Test the search with a pagination
        $this->checkGetTableListSearchWithPagination('a', 'cronJob');
    }

    /**
     * Tests the searchField config pagination
     */
    public function testSearchFieldConfig(): void
    {
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->checkTableListConfig('searchField');

        $this->reset();

        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->checkGetTableListConfigPagination('searchField');
    }

    /**
     * Tests the cron job search
     */
    public function testSearchFieldSearchConfig(): void
    {
        $sql = 'SELECT count(*)
                FROM s_search_fields f
                LEFT JOIN s_search_tables t on f.tableID = t.id';
        $totalCronJobCount = (int) $this->getContainer()->get(Connection::class)->fetchOne($sql);

        $this->checkGetTableListSearch('b', $totalCronJobCount, 'searchField');

        $this->reset();

        $this->checkGetTableListSearchWithPagination('b', 'searchField');
    }

    /**
     * Tests the existence of the document type key
     */
    public function testPersistDocumentTypeKey(): void
    {
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();

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
        $body = $response->getBody();
        static::assertIsString($body);

        static::assertTrue(json_decode($body, true)['success']);

        $this->getContainer()->get(Connection::class)->executeQuery('DELETE FROM `s_core_documents` WHERE `key`="first_test_document";');
    }

    /**
     * Tests whether the list of pdf documents includes its translations
     */
    public function testIfPDFDocumentsListIncludesTranslation(): void
    {
        // Set up
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth(false);
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl();

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
        $body = $response->getBody();
        static::assertIsString($body);

        $responseJSON = json_decode($body, true);
        static::assertTrue($responseJSON['success']);

        foreach ($responseJSON['data'] as $documentType) {
            static::assertEquals($documentType['name'], $documentType['description']);
        }

        $this->reset();
        $this->getContainer()->reset('translation');

        // Check for English translations
        $user = $this->getContainer()->get('auth')->getIdentity();
        $user->locale = $this->getContainer()->get('models')->getRepository(
            Locale::class
        )->find(2);

        $this->Request()->setMethod('GET');
        $getString = http_build_query($getParams);
        $response = $this->dispatch('backend/Config/getList?' . $getString);
        $body = $response->getBody();
        static::assertIsString($body);

        $responseJSON = json_decode($body, true);
        static::assertTrue($responseJSON['success']);

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
     * @param array<string, mixed> $elementData
     *
     * @dataProvider getSaveElementData
     */
    public function testSaveElement(array $elementData): void
    {
        $saveElementMethod = TestReflectionHelper::getMethod(Shopware_Controllers_Backend_Config::class, 'saveElement');
        $configController = new Shopware_Controllers_Backend_Config();
        $configController->setContainer($this->getContainer());

        $eventCalled = false;
        $this->getContainer()->get('events')->addListener(
            'Shopware_Controllers_Backend_Config_After_Save_Config_Element',
            function () use (&$eventCalled) {
                $eventCalled = true;
            }
        );
        $shop = $this->createMock(Shop::class);
        $saveElementMethod->invokeArgs($configController, [$elementData, $shop]);

        static::assertTrue($eventCalled);
    }

    public function getSaveElementData(): Generator
    {
        $sql = 'SELECT id FROM s_core_config_elements';
        $elementId = (int) $this->getContainer()->get(Connection::class)->fetchOne($sql);

        yield 'Save button' => [
            [
                'id' => $elementId,
                'name' => 'basicSettingsGroup',
                'value' => null,
                'label' => 'Grundeinstellungen',
                'description' => null,
                'type' => 'button',
                'required' => false,
                'scope' => 1,
                'options' => [],
                'values' => [
                    [
                        'id' => null,
                        'shopId' => 1,
                        'value' => null,
                    ],
                    [
                        'id' => null,
                        'shopId' => 2,
                        'value' => null,
                    ],
                ],
            ],
        ];
        yield 'Save multi select' => [
            [
                'id' => $elementId,
                'name' => 'multiselect',
                'value' => 'one',
                'label' => 'multiselect',
                'description' => null,
                'type' => 'select',
                'required' => false,
                'scope' => 1,
                'options' => [
                    'multiSelect' => true,
                    'store' => [
                        [
                            'one',
                            'One',
                        ],
                        [
                            'two',
                            'Two',
                        ],
                        [
                            'three',
                            'Three',
                        ],
                    ],
                    'queryMode' => 'remote',
                ],
                'values' => [
                    [
                        'id' => null,
                        'shopId' => 1,
                        'value' => [
                            'one',
                        ],
                    ],
                    [
                        'id' => null,
                        'shopId' => 2,
                        'value' => [
                            'one',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests the config tableList
     */
    private function checkTableListConfig(string $tableListName): void
    {
        // Should return more than 2 items
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/Config/getTableList/_repositoryClass/' . $tableListName);
        $returnData = $this->View()->getAssign('data');
        static::assertGreaterThan(2, \count($returnData));
        static::assertTrue($this->View()->getAssign('success'));
    }

    /**
     * Tests the config table list with pagination
     */
    private function checkGetTableListConfigPagination(string $tableListName): void
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
     */
    private function checkGetTableListSearch(string $searchTerm, int $totalCount, string $tableListName): void
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
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->dispatch($url . $query);
        $returnData = $this->View()->getAssign('data');
        static::assertGreaterThan(0, \count($returnData));
        static::assertLessThan($totalCount, \count($returnData));
        static::assertTrue($this->View()->getAssign('success'));
    }

    /**
     * Checks the search and the pagination of the table list config
     */
    private function checkGetTableListSearchWithPagination(string $searchTerm, string $tableListName): void
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
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->dispatch($url . $query);
        $returnData = $this->View()->getAssign('data');
        static::assertCount(2, $returnData);
        static::assertTrue($this->View()->getAssign('success'));
    }
}
