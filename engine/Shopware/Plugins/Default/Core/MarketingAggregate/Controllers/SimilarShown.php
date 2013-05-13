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
 * @package   Shopware\Plugins\MarketingAggregate\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_SimilarShown extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get access on the SimilarShown component.
     *
     * @return Shopware_Components_SimilarShown
     */
    public function SimilarShown()
    {
        return Shopware()->SimilarShown();
    }


    /**
     * Controller action which can be access over an ajax request.
     * This function is used to get the also bought count.
     */
    public function getSimilarShownCountAction()
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                article1.articleID as article_id,
                article2.articleID as related_article_id,
                COUNT(article2.articleID) as viewed,
                now() as init_date
            FROM s_emarketing_lastarticles article1
               INNER JOIN s_emarketing_lastarticles article2
                  ON  article1.sessionID  = article2.sessionID
                  AND article1.articleID != article2.articleID
            GROUP BY article1.articleID, article2.articleID
            LIMIT 0, 1
        ";

        $data = Shopware()->Db()->fetchRow($sql);
        $count = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");

        $this->View()->assign(array('success' => true, 'data' => array('count' => $count)));
    }

    /**
     * Helper function to initials the s_articles_also_bought table.
     * This table is used for the new shopware also bought articles function.
     */
    public function initSimilarShownAction()
    {
        $offset = $this->Request()->get('offset');
        $limit = $this->Request()->get('limit');

        if ($offset === 0) {
            $sql = "DELETE FROM s_articles_similar_shown";
            Shopware()->Db()->query($sql);
        }

        $this->SimilarShown()->initSimilarShown($offset, $limit);

        $this->View()->assign(array('success' => true));
    }
}

