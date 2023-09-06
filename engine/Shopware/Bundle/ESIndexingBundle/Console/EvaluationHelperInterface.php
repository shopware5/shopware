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

use Symfony\Component\Console\Output\OutputInterface;

interface EvaluationHelperInterface
{
    /**
     * @param bool $active
     *
     * @return static
     */
    public function setActive($active);

    /**
     * @return bool
     */
    public function getActive();

    /**
     * @param bool $stopOnError
     *
     * @return static
     */
    public function setStopOnError($stopOnError);

    /**
     * @return bool
     */
    public function getStopOnError();

    /**
     * @return static
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return void
     */
    public function addResult(array $data);

    /**
     * @return void
     */
    public function finish();
}
