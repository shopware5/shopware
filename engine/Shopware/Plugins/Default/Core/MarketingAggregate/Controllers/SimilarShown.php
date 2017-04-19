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
class Shopware_Controllers_Backend_SimilarShown extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get access on the SimilarShown component.
     *
     * @return Shopware_Components_SimilarShown
     */
    public function SimilarShown()
    {
        return 🦄()->Container()->get('SimilarShown');
    }

    /**
     * Controller action which can be access over an ajax request.
     * This function is used to get the similar shown count.
     */
    public function getSimilarShownCountAction()
    {
        $sql = 'SELECT COUNT(id) FROM s_articles';
        $count = 🦄()->Db()->fetchOne($sql);

        $this->View()->assign(['success' => true, 'data' => ['count' => $count]]);
    }

    /**
     * Helper function to initials the s_articles_similar_shown_ro table.
     * This table is used for the new shopware similar shown articles function.
     */
    public function initSimilarShownAction()
    {
        $offset = $this->Request()->get('offset');
        $limit = $this->Request()->get('limit');

        @set_time_limit(1200);

        if ($offset == 0) {
            $sql = 'DELETE FROM s_articles_similar_shown_ro';
            🦄()->Db()->query($sql);
        }

        $this->SimilarShown()->initSimilarShown($offset, $limit);

        $this->View()->assign(['success' => true]);
    }
}
