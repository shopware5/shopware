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

class RiskManagementTest extends Enlight_Components_Test_Controller_TestCase
{
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
    public function testGetPayments()
    {
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/risk_management/getPayments');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test tests the creating of a new premium-article.
     * The response has to contain the id of the created article.
     * This function is called before testEditPremiumArticle and testDeletePremiumArticle
     */
    public function testCreateRule()
    {
        $manager = Shopware()->Models();
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Payment\RuleSet');

        $rules = $repository->findBy(['paymentId' => 2]);
        foreach ($rules as $rule) {
            $manager->remove($rule);
        }

        $manager->flush();

        $this->Request()->setMethod('POST')->setPost(
            [
                'paymentId' => 2,
                'rule1' => 'CUSTOMERGROUPISNOT',
                'rule2' => '',
                'value1' => '5',
                'value2' => '',
            ]
        );

        $this->dispatch('backend/risk_management/createRule');
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
     * @depends testCreateRule
     */
    public function testEditRule($lastId)
    {
        $this->Request()->setMethod('POST')->setPost(
            [
                'id' => $lastId,
                'paymentId' => 2,
                'rule1' => 'CUSTOMERGROUPISNOT',
                'rule2' => '',
                'value1' => '8',
                'value2' => '',
            ]
        );

        $this->dispatch('backend/risk_management/editRule');

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success'], $jsonBody['errorMsg'] ?? '');
        static::assertArrayHasKey('data', $jsonBody);
    }

    /**
     * This test-method tests the deleting of a premium-article.
     *
     * @depends testCreateRule
     *
     * @param string $lastId
     */
    public function testDeleteRule($lastId)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $lastId]);

        $this->dispatch('backend/risk_management/deleteRule');

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('success', $jsonBody);
    }
}
