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
 * Also bought component which contains all logic about the shopware
 * Also bought functions.
 * This components refresh and initials the Also bought data
 * in the s_articles_also_bought_ro
 */
class Shopware_Components_AlsoBought extends Enlight_Class
{
    /**
     * This function initials the also bought marketing data.
     * The passed offset and limit is used to select a data set
     * of articles.
     * The articles are used for the aggregate query which is
     * faster if an constant where condition is used.
     *
     * @param int|null $offset
     * @param int|null $limit
     */
    public function initAlsoBought($offset = null, $limit = null)
    {
        $sql = 'SELECT id FROM s_articles ';
        if ($limit !== null) {
            $sql = Shopware()->Db()->limit($sql, $limit, $offset);
        }

        $articles = Shopware()->Db()->fetchCol($sql);

        $preparedSelect = Shopware()->Db()->prepare('
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
                  AND detail2.articleID > 0
                  AND detail1.articleID = :articleId
            GROUP BY detail2.articleID
        ');

        $preparedInsert = Shopware()->Db()->prepare('
            INSERT IGNORE INTO s_articles_also_bought_ro (article_id, related_article_id, sales)
            VALUES (:article_id, :related_article_id, :sales);
        ');

        // Iterate all selected articles which has to be initialed
        foreach ($articles as $articleId) {
            // Now we select all bought articles for the current article id
            $preparedSelect->execute(['articleId' => $articleId]);
            $combinations = $preparedSelect->fetchAll();

            // At least we have to insert each combination in the aggregate s_articles_also_bought_ro table.
            foreach ($combinations as $combination) {
                $preparedInsert->execute($combination);
            }
        }
    }

    /**
     * This function is used to insert or update the bought articles table
     * for a single buy combination of two articles.
     *
     * @param int $articleId
     * @param int $relatedArticleId
     */
    public function refreshBoughtArticles($articleId, $relatedArticleId)
    {
        $sql = '
            INSERT INTO s_articles_also_bought_ro (article_id, related_article_id, sales)
            VALUES (:articleId, :relatedArticleId, 1)
            ON DUPLICATE KEY UPDATE sales = sales + 1;
        ';

        Shopware()->Db()->query($sql, [
            'articleId' => $articleId,
            'relatedArticleId' => $relatedArticleId,
        ]);
    }

    /**
     * This function is used to insert or update the bought articles table
     * for multiple buy combinations.
     *
     * @param array $combinations
     */
    public function refreshMultipleBoughtArticles($combinations = [])
    {
        if (empty($combinations)) {
            return;
        }

        $sqlCombinations = [];
        $sql = 'INSERT INTO s_articles_also_bought_ro (article_id, related_article_id, sales) VALUES ';

        foreach ($combinations as $combination) {
            $sqlCombinations[] = '(' . (int) $combination['article_id'] . ', ' . (int) $combination['related_article_id'] . ', 1)';
        }

        $sql .= implode(',', $sqlCombinations);

        $sql .= ' ON DUPLICATE KEY UPDATE sales = sales + 1;';

        Shopware()->Db()->query($sql);
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     *
     * Helper function to get the date of one year ago.
     *
     * @param int $interval
     *
     * @return DateTime
     */
    public function getOrderTime($interval = 365)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $orderTime = new DateTime();
        $orderTime->sub(new DateInterval('P' . (int) $interval . 'D'));

        return $orderTime;
    }
}
