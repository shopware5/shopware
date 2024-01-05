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

use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexer;

class Shopware_Controllers_Backend_SearchIndex extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * This controller action is used to build the search index.
     */
    public function buildAction()
    {
        @set_time_limit(1200);

        $indexer = $this->get(SearchIndexer::class);
        $indexer->build();

        $this->View()->assign(['success' => true]);
    }
}
