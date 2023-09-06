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

namespace Shopware\Components;

use Monolog\DateTimeImmutable;
use Monolog\Logger as BaseLogger;

class Logger extends BaseLogger
{
    /**
     * @param string|array $label
     * @param array|null   $data
     */
    public function table($label, $data = null)
    {
        if (\is_array($label)) {
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

    public function addRecord(int $level, string $message, array $context = [], ?DateTimeImmutable $datetime = null): bool
    {
        $message = str_replace(['[', ']'], ['(', ')'], $message);

        return parent::addRecord($level, $message, $context, $datetime);
    }
}
