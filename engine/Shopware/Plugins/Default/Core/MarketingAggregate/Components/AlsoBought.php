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
 * Also bought component which contains all logic about the shopware
 * Also bought functions.
 * This components refresh and initials the Also bought data
 * in the s_articles_also_bought_ro
 *
 * @category  Shopware
 * @package   Shopware\Plugins\MarketingAggregate\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_AlsoBought extends Enlight_Class
{
    /**
     * This function initials the also bought marketing data.
     */
    public function initAlsoBought($offset = null, $limit = null)
    {
        $limitSql = "";
        if ($limit !== null && $offset !== null) {
            $limitSql = " LIMIT " . $offset . " , " . $limit;
        }

        $sql = "
            INSERT INTO s_articles_also_bought_ro (article_id, related_article_id, sales)
            SELECT
                detail1.articleID as article_id,
                detail2.articleID as related_article_id,
                COUNT(detail2.articleID) as sales
            FROM s_order_details detail1
               INNER JOIN s_order_details detail2
                  ON detail1.orderID = detail2.orderID
                  AND detail1.articleID != detail2.articleID
                  AND detail1.modus = 0
                  AND detail2.modus = 0
                  AND detail1.articleID > 0
                  AND detail2.articleID > 0
            GROUP BY detail1.articleID, detail2.articleID
            $limitSql
        ";
        Shopware()->Db()->query($sql);
    }

    /**
     * This function is used to insert or update the bought articles table
     * for a single buy combination of two articles.
     *
     * @param $articleId
     * @param $relatedArticleId
     */
    public function refreshBoughtArticles($articleId, $relatedArticleId)
    {
        $sql = "
            INSERT INTO s_articles_also_bought_ro (article_id, related_article_id, sales)
            VALUES (:articleId, :relatedArticleId, 1)
            ON DUPLICATE KEY UPDATE sales = sales + 1;
        ";

        Shopware()->Db()->query($sql, array(
            'articleId' => $articleId,
            'relatedArticleId' => $relatedArticleId
        ));
    }
}