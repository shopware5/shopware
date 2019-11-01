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

namespace Shopware\Tests\Functional\Api;

/**
 * @covers \Shopware_Controllers_Api_Articles
 */
class ArticleTest extends AbstractApiTestCase
{
    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/articles/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(401, $response->getStatusCode());

        $result = $response->getContent();

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
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostArticlesShouldBeSuccessful(): string
    {
        $requestData = [
            'name' => 'Testartikel',
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
                        'name' => 'neueOption' . uniqid(mt_rand(), true),
                    ],
                ],
            ],

            'mainDetail' => [
                'number' => 'swTEST' . uniqid(mt_rand(), true),
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

            'configuratorSet' => [
                'name' => 'MeinKonf',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'Grün'],
                        ],
                    ],
                    [
                        'name' => 'Gräße',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],

            'images' => [
                ['link' => 'http://assets.shopware.com/sw_logo_white.png'],
                ['link' => 'http://assets.shopware.com/sw_logo_white.png'],
            ],

            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid(mt_rand(), true),
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
                    'number' => 'swTEST.variant.' . uniqid(mt_rand(), true),
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
        static::assertEquals(null, $response->headers->get(('Set-Cookie')));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        $identifier = array_pop(explode('/', $location));

        static::assertGreaterThan(0, (int) $identifier);

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
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testGetArticlesWithIdShouldBeSuccessful(string $id): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('active', $data);

        static::assertEquals('Testartikel', $data['name']);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testPutArticlesWithInvalidDataShouldReturnError($id): void
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
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testPutArticlesShouldBeSuccessful(string $id): void
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
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $this->authenticatedApiRequest('GET', '/api/articles/' . $id, []);
        $response = $this->client->getResponse();

        $result = $response->getContent();
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
        static::assertEquals(0, count($article['similar']));
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     *
     * @param int $id
     */
    public function testChangeVariantArticleMainVariantShouldBeSuccessful(string $id): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
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
            static::assertEquals(null, $response->headers->get('Set-Cookie'));
            static::assertEquals(200, $response->getStatusCode());
            $result = $response->getContent();
            $result = json_decode($result, true);
            static::assertArrayHasKey('success', $result);
            static::assertTrue($result['success']);

            $this->authenticatedApiRequest('GET', '/api/articles/' . $id, []);
            $response = $this->client->getResponse();

            static::assertEquals('application/json', $response->headers->get('Content-Type'));
            static::assertEquals(null, $response->headers->get('Set-Cookie'));
            static::assertEquals(200, $response->getStatusCode());
            $result = $response->getContent();
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
    public function testReplaceArticleImagesWithUrlAndMediaId($articleId): void
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
                    'link' => 'http://assets.shopware.com/sw_logo_white.png',
                ],
                [
                    'mediaId' => 46,
                ],
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $articleId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertEquals($articleId, $data['id']);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     */
    public function testReplaceArticleImagesWithInvalidPayload($articleId): void
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

        $this->authenticatedApiRequest('PUT', '/api/articles/' . $articleId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostArticlesShouldBeSuccessful
     *
     * @param int $id
     *
     * @return
     */
    public function testDeleteArticlesShouldBeSuccessful($id): int
    {
        $this->authenticatedApiRequest('DELETE', '/api/articles/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteArticlesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('DELETE', '/api/articles/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
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
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetArticlesShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/articles/', []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getBody();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        static::assertArrayHasKey('total', $response);
        static::assertIsInt($response['total']);
    }

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
                          'name' => 'neueOption' . uniqid(mt_rand(), true),
                      ],
                  ],
              ],

              'mainDetail' => [
                  'number' => 'swTEST' . uniqid(mt_rand(), true),
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
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertEquals('create', $result['data'][0]['operation']);
        static::assertEquals('create', $result['data'][1]['operation']);
        static::assertEquals('create', $result['data'][2]['operation']);
        static::assertEquals('update', $result['data'][3]['operation']);
    }
}
