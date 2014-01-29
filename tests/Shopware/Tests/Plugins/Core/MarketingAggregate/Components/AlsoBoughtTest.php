<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Plugins_Core_MarketingAggregate_Components_AlsoBoughtTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

    protected function getAllAlsoBought($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles_also_bought_ro ' . $condition);
    }

    protected function resetAlsoBought($condition = '')
    {
        $this->Db()->query("DELETE FROM s_articles_also_bought_ro " . $condition);
    }


    public function testInitAlsoBought()
    {
        $this->resetAlsoBought();
        $this->AlsoBought()->initAlsoBought();

        $this->assertCount(42, $this->getAllAlsoBought());
    }

    public function testRefreshBoughtArticles()
    {
        $this->resetAlsoBought();
        $this->AlsoBought()->initAlsoBought();

        $combinations = $this->getAllAlsoBought();
        foreach($combinations as $combination) {
            $this->AlsoBought()->refreshBoughtArticles(
                $combination['article_id'],
                $combination['related_article_id']
            );
            $updated = $this->getAllAlsoBought(
                " WHERE article_id = " . $combination['article_id'] .
                " AND related_article_id = " . $combination['related_article_id']
            );
            $updated = $updated[0];

            $this->assertNotEmpty($updated);
            $this->assertEquals($combination['sales'] + 1, $updated['sales']);
        }
    }
}