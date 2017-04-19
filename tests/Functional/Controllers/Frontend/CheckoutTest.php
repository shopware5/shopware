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
class Shopware_Tests_Controllers_Frontend_CheckoutTest extends Enlight_Components_Test_Plugin_TestCase
{
    const ARTICLE_NUMBER = 'SW10239';
    const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    /**
     * reads the user agent black list and test if the bot can add an article
     *
     * @ticket SW-6411
     */
    public function testBotAddBasketArticle()
    {
        $botBlackList = ['digout4u', 'fast-webcrawler', 'googlebot', 'ia_archiver', 'w3m2', 'frooglebot'];
        foreach ($botBlackList as $userAgent) {
            if (!empty($userAgent)) {
                $sessionId = $this->addBasketArticle($userAgent);
                $this->assertNotEmpty($sessionId);
                $basketId = Shopware()->Db()->fetchOne(
                    'SELECT id FROM s_order_basket WHERE sessionID = ?',
                    [$sessionId]
                );
                $this->assertEmpty($basketId);
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
    }

    /**
     * test if an normal user can add an article
     *
     * @ticket SW-6411
     */
    public function testAddBasketArticle()
    {
        $sessionId = $this->addBasketArticle(self::USER_AGENT);
        $this->assertNotEmpty($sessionId);
        $basketId = Shopware()->Db()->fetchOne(
            'SELECT id FROM s_order_basket WHERE sessionID = ?',
            [$sessionId]
        );
        $this->assertNotEmpty($basketId);

        Shopware()->Modules()->Basket()->sDeleteBasket();
    }

    /**
     * fires the add article request with the given user agent
     *
     * @param $userAgent
     *
     * @return string | session id
     */
    private function addBasketArticle($userAgent)
    {
        $this->reset();
        $this->Request()->setHeader('User-Agent', $userAgent);
        $this->dispatch('/checkout/addArticle/sAdd/' . self::ARTICLE_NUMBER);

        return Shopware()->Container()->get('SessionID');
    }
}
