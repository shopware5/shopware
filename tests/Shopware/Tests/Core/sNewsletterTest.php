<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

class sNewsletterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sNewsletter
     */
    private static $module;

    private static $emarketingContainerID;

    private static $campaignContainerID;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$module = Shopware()->Modules()->Newsletter();

        Shopware()->Db()->insert('s_campaigns_containers', array(
            'type' => 'ctSuggest',
            'position' => 1,
            'value' => '10',
            'description' => 'test newsletter container',
            'promotionID' => '1'
        ));
        self::$campaignContainerID = Shopware()->Db()->lastInsertId('s_campaigns_containers');

        Shopware()->Db()->insert('s_emarketing_lastarticles', array(
            'name' => 'Test',
            'articleID' => 2,
            'sessionID' => 'Test'
        ));
        self::$emarketingContainerID = Shopware()->Db()->lastInsertId('s_campaigns_containers');
    }

    public static function tearDownAfterClass()
    {
        Shopware()->Db()->delete('s_campaigns_containers', "id = ".self::$campaignContainerID);
        Shopware()->Db()->delete('s_emarketing_lastarticles', "id = ".self::$emarketingContainerID);

        parent::tearDownAfterClass();
    }

    public function testGetBasicSuggestions()
    {
        $campaignSuggestions = self::$module->sCampaignsGetSuggestions(1);

        $this->assertEquals('test newsletter container', $campaignSuggestions['description']);
        $this->assertEquals(10, $campaignSuggestions['value']);
        $this->assertGreaterThanOrEqual(1, count($campaignSuggestions['data']));
        $this->assertLessThanOrEqual(10, count($campaignSuggestions['data']));

        foreach ($campaignSuggestions['data'] as $articleData) {
            $this->assertArrayHasKey('articleID', $articleData);
            $this->assertArrayHasKey('articleDetailsID', $articleData);
            $this->assertArrayHasKey('ordernumber', $articleData);
            $this->assertArrayHasKey('articleName', $articleData);
            $this->assertArrayHasKey('taxID', $articleData);
            $this->assertArrayHasKey('price', $articleData);
        }
    }
}
