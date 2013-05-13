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
class Shopware_Controllers_Backend_AlsoBought extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get access on the AlsoBought component.
     *
     * @return Shopware_Components_AlsoBought
     */
    public function AlsoBought()
    {
        return Shopware()->AlsoBought();
    }


    /**
     * Controller action which can be access over an ajax request.
     * This function is used to get the also bought count.
     */
    public function getAlsoBoughtCountAction()
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
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
    public function initAlsoBoughtAction()
    {
        $offset = $this->Request()->get('offset');
        $limit = $this->Request()->get('limit');

        if ($offset === 0) {
            $sql = "DELETE FROM s_articles_also_bought";
            Shopware()->Db()->query($sql);
        }

        $this->AlsoBought()->initAlsoBought($offset, $limit);

        $this->View()->assign(array('success' => true));
    }
}

