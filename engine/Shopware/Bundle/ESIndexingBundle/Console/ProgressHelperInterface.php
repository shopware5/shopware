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

namespace Shopware\Bundle\ESIndexingBundle\Console;

interface ProgressHelperInterface
{
    /**
     * Initials the progress with the provided count.
     * Allows to provide a label to display a message before the progress starts
     *
     * @param int    $count
     * @param string $label
     *
     * @return void
     */
    public function start($count, $label = '');

    /**
     * Advance the progress with the provided value
     *
     * @param int $step
     *
     * @return void
     */
    public function advance($step = 1);

    /**
     * Finish the progress bar
     *
     * @return void
     */
    public function finish();
}
