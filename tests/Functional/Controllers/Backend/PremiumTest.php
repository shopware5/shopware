<?php
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

use Enlight_Components_Test_Controller_TestCase;

class PremiumTest extends Enlight_Components_Test_Controller_TestCase
{
    private $premiumData = [
        'orderNumber' => 'SW2001_test',
        'pseudoOrderNumber' => 'SW123',
        'startPrice' => 123,
        'shopId' => 1,
    ];

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Tests the getPremiumArticlesAction()
     * to test if reading the articles is working
     * Additionally this method tests the search-function
     */
    public function testGetPremiumArticles()
    {
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/premium/getPremiumArticles');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);

        // Testing the search-function
        $filter = [
            'filter' => json_encode([[
                'value' => 'test',
            ]]),
        ];
        $this->Request()->setMethod('POST')->setPost($filter);
        $this->dispatch('backend/premium/getPremiumArticles');
        $jsonBody = $this->View()->getAssign();
        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test tests the creating of a new premium-article.
     * The response has to contain the id of the created article.
     * This function is called before testEditPremiumArticle and testDeletePremiumArticle
     *
     * @return string
     */
    public function testCreatePremiumArticle()
    {
        $this->Request()->setMethod('POST')->setPost($this->premiumData);

        $this->dispatch('backend/premium/createPremiumArticle');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('id', $jsonBody['data']);

        return $jsonBody['data']['id'];
    }

    /**
     * This test method tests the editing of
     * a premium-article.
     * The testCreatePremiumArticle method is called before.
     *
     * @param string $lastId The id of the last created article
     *
     * @depends testCreatePremiumArticle
     */
    public function testEditPremiumArticle($lastId)
    {
        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();

        $premiumData = $this->premiumData;
        $premiumData['pseudoOrderNumber'] = 'SW987';
        $premiumData['id'] = $lastId;

        $this->Request()->setMethod('POST')->setPost($premiumData);

        $this->dispatch('backend/premium/editPremiumArticle');

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test-method tests the deleting of a premium-article.
     *
     * @depends testCreatePremiumArticle
     *
     * @param string $lastId
     */
    public function testDeletePremiumArticle($lastId)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $lastId]);

        $this->dispatch('backend/premium/deletePremiumArticle');

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('success', $jsonBody);
    }
}
