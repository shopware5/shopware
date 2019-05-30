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

namespace Shopware\Components;

use Monolog\Logger as BaseLogger;

class Logger extends BaseLogger
{
    /**
     * @param string|array $label
     * @param array|null   $data
     */
    public function table($label, $data = null)
    {
        if (is_array($label)) {
            list($label, $data) = $label;
        }

        $this->log(BaseLogger::DEBUG, $label, ['table' => $data]);
    }

    /**
     * @param string $label
     */
    public function trace($label)
    {
        $this->log(BaseLogger::DEBUG, $label, ['trace' => true]);
    }

    public function addRecord($level, $message, array $context = [])
    {
        $message = str_replace(['[', ']'], ['(', ')'], $message);

        return parent::addRecord($level, $message, $context);
    }
}
