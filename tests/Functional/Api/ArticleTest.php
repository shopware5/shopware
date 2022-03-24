<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Api;

use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Property\Option;
use Shopware\Tests\Functional\Traits\ContainerTrait;

/**
 * @covers \Shopware_Controllers_Api_Articles
 */
class ArticleTest extends AbstractApiTestCase
{
    use ContainerTrait;

    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/articles/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(401, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetArticlesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostArticlesShouldBeSuccessful(): int
    {
        $requestData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',

            'filterGroupId' => 1,

            'propertyValues' => [
                [
                    'value' => 'grün',
                    'option' => [
                        'name' => 'Farbe',
                    ],
                ],
                [
                    'value' => 'testWert',
                    'option' => [
                        'name' => 'neueOption' . uniqid((string) mt_rand(), true),
                    ],
                ],
            ],

            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand(), true),
                'inStock' => 15,
                'unitId' => 1,

                'attribute' => [
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ],

                'minPurchase' => 5,
                'purchaseSteps' => 2,

                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                        'regulationPrice' => 25,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                        'regulationPrice' => 25,
                    ],
                ],
            ],

            'configuratorSet' => [
                'name' => 'MyConfigurator',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'Grün'],
                        ],
                    ],
                    [
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],

            'images' => [
                ['link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC'],
                ['link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC'],
            ],

            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand(), true),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],

                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],

                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],

                    'minPurchase' => 5,
                    'purchaseSteps' => 2,

                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand(), true),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],

                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],

                    'configuratorOptions' => [
                        [
                            'option' => 'Grün',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],

                    'minPurchase' => 5,
                    'purchaseSteps' => 2,

                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],

            'taxId' => 1,
            'supplierId' => 2,

            'similar' => [
                ['id' => 5],
                ['id' => 6],
            ],

            'categories' => [
                ['id' => 15],
                ['id' => 10],
            ],

            'related' => [
                ['id' => 3, 'cross' => true],
                ['id' => 4],
            ],

            'links' => [
                ['name' => 'foobar', 'link' => 'http://example.org'],
                ['name' => 'Video', 'link' => 'http://example.org'],
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/articles/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        static::assertIsString($location);
        $locationPars = explode('/', $location);
        $identifier = (int) array_pop($locationPars);

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    public function testPostArticlesWithInvalidDataShouldReturnError(): void
    {
        $requestData = [
            'test' => true,
        ];

        $this->authenticatedApiRequest('POST', '/api/articles/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testGetArticlesWithIdShouldBeSuccessful(int $id): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('active', $data);

        static::assertEquals(25, $data['mainDetail']['prices'][0]['regulationPrice']);
        static::assertEquals('Testartikel', $data['name']);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testPutArticlesWithInvalidDataShouldReturnError(int $id): void
    {
        // required field name is blank
        $testData = [
            'name' => ' ',
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $id, [], $testData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testPutArticlesShouldBeSuccessful(int $id): void
    {
        $testData = [
            'name' => 'Update',
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',

            // update supplier id
            'supplierId' => 3,

            // categories should be replaced
            'categories' => [
                ['id' => 16],
            ],

            'filterGroupId' => 1,

            // values should be replaced
            'propertyValues' => [
            ],

            // related is not included, therefore it stays untouched

            // similar is set to empty array, therefore it should be cleared
            'similar' => [],
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $id, [], $testData);
        $response = $this->client->getResponse();

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('Set-Cookie'),
            'There should be no set-cookie header set.'
        );
        static::assertNull(
            $response->headers->get('location'),
            'There should be no location header set.'
        );

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $this->authenticatedApiRequest('GET', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        $article = $result['data'];

        static::assertEquals($id, $article['id']);
        static::assertEquals($testData['description'], $article['description']);
        static::assertEquals($testData['descriptionLong'], $article['descriptionLong']);
        static::assertEquals($testData['supplierId'], $article['supplier']['id']);

        // Categories should be updated
        static::assertCount(1, $article['categories']);

        // Related should be untouched
        static::assertCount(2, $article['related']);

        // Similar should be removed
        static::assertCount(0, $article['similar'] ?? []);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testChangeVariantArticleMainVariantShouldBeSuccessful(int $id): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        $variantNumbers = array_map(static function ($item) {
            return $item['number'];
        }, $result['data']['details']);

        $oldMain = $result['data']['mainDetail']['number'];

        foreach ($variantNumbers as $variantNumber) {
            $testData = [
                'variants' => [
                    [
                        'number' => $variantNumber,
                        'isMain' => true,
                    ],
                ],
            ];

            $this->authenticatedApiRequest('PUT', '/api/articles/' . $id, [], $testData);
            $response = $this->client->getResponse();

            static::assertEquals('application/json', $response->headers->get('Content-Type'));
            static::assertNull($response->headers->get('Set-Cookie'));
            static::assertEquals(200, $response->getStatusCode());
            $result = $response->getContent();
            static::assertIsString($result);
            $result = json_decode($result, true);
            static::assertArrayHasKey('success', $result);
            static::assertTrue($result['success']);

            $this->authenticatedApiRequest('GET', '/api/articles/' . $id);
            $response = $this->client->getResponse();

            static::assertEquals('application/json', $response->headers->get('Content-Type'));
            static::assertNull($response->headers->get('Set-Cookie'));
            static::assertEquals(200, $response->getStatusCode());
            $result = $response->getContent();
            static::assertIsString($result);
            $result = json_decode($result, true);

            static::assertEquals($variantNumber, $result['data']['mainDetail']['number']);

            foreach ($result['data']['details'] as $variantData) {
                if ($variantData['number'] === $oldMain) {
                    static::assertEquals(2, $variantData['kind']);
                }
            }

            $oldMain = $result['data']['mainDetail']['number'];
        }
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testReplaceArticleImagesWithUrlAndMediaId(int $productId): void
    {
        $requestData = [
            '__options_images' => [
                'replace' => 1,
            ],
            'images' => [
                [
                    'mediaId' => 44,
                ],
                [
                    'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
                ],
                [
                    'mediaId' => 46,
                ],
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $productId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertEquals($productId, $data['id']);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testReplaceArticleImagesWithInvalidPayload(int $productId): void
    {
        $requestData = [
            '__options_images' => [
                'replace' => 1,
            ],
            'images' => [
                [
                    'id' => 999999,
                    'mediaId' => 44,
                ],
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $productId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testDeleteArticlesShouldBeSuccessful(int $id): int
    {
        $this->authenticatedApiRequest('DELETE', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteArticlesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('DELETE', '/api/articles/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutArticlesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $requestData = [
            'active' => true,
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetArticlesShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/');
        $response = $this->client->getResponse();
        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);
        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertNull($response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getBody();
        static::assertIsString($response);
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        static::assertArrayHasKey('total', $response);
        static::assertIsInt($response['total']);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSimpleArticleData(): array
    {
        return [
              'name' => 'Simple test article',
              'description' => 'Test description',
              'descriptionLong' => 'Test descriptionLong',
              'active' => true,
              'pseudoSales' => 999,
              'highlight' => true,
              'keywords' => 'test, testarticle',

              'filterGroupId' => 1,

              'propertyValues' => [
                  [
                      'value' => 'grün',
                      'option' => [
                          'name' => 'Farbe',
                      ],
                  ],
                  [
                      'value' => 'testWert',
                      'option' => [
                          'name' => 'neueOption' . uniqid((string) mt_rand(), true),
                      ],
                  ],
              ],

              'mainDetail' => [
                  'number' => 'swTEST' . uniqid((string) mt_rand(), true),
                  'inStock' => 15,
                  'unitId' => 1,

                  'attribute' => [
                      'attr1' => 'Freitext1',
                      'attr2' => 'Freitext2',
                  ],

                  'minPurchase' => 5,
                  'purchaseSteps' => 2,

                  'prices' => [
                      [
                          'customerGroupKey' => 'EK',
                          'from' => 1,
                          'to' => 20,
                          'price' => 500,
                      ],
                      [
                          'customerGroupKey' => 'EK',
                          'from' => 21,
                          'to' => '-',
                          'price' => 400,
                      ],
                  ],
              ],

              'taxId' => 1,
              'supplierId' => 2,

              'similar' => [
                  ['id' => 5],
                  ['id' => 6],
              ],

              'categories' => [
                  ['id' => 15],
                  ['id' => 10],
              ],

              'related' => [
                  ['id' => 3, 'cross' => true],
                  ['id' => 4],
              ],

              'links' => [
                  ['name' => 'foobar', 'link' => 'http://example.org'],
                  ['name' => 'Video', 'link' => 'http://example.org'],
              ],
          ];
    }

    public function testBatchModeShouldBeSuccessful(): void
    {
        $data = [
            $this->getSimpleArticleData(),
            $this->getSimpleArticleData(),
            $this->getSimpleArticleData(),
            [
                'id' => 2,
                'keywords' => 'batch test',
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/', [], $data);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertEquals('create', $result['data'][0]['operation']);
        static::assertEquals('create', $result['data'][1]['operation']);
        static::assertEquals('create', $result['data'][2]['operation']);
        static::assertEquals('update', $result['data'][3]['operation']);
    }

    public function testPropertyOptionFilterableIsSetCorrectly(): void
    {
        $productIdAperitif = 3;
        $optionIdAlcoholAmount = 1;
        $data = [
            'propertyValues' => [
                [
                    'value' => 'foo',
                    'option' => [
                        'id' => $optionIdAlcoholAmount,
                    ],
                ],
            ],
        ];
        $this->authenticatedApiRequest('PUT', sprintf('/api/articles/%s', $productIdAperitif), [], $data);
        $response = $this->client->getResponse();
        static::assertEquals(200, $response->getStatusCode());
        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);
        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $option = $this->getContainer()->get(ModelManager::class)->getRepository(Option::class)->find($optionIdAlcoholAmount);
        static::assertInstanceOf(Option::class, $option);
        static::assertTrue($option->isFilterable());
    }
}
