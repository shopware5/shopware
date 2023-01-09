<?php

declare(strict_types=1);

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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1722 extends AbstractMigration
{
    public function up($modus)
    {
        $selectSql = 'SELECT * FROM `s_core_config_elements` WHERE `name` = "botBlackList"';
        $updateSql = 'UPDATE `s_core_config_elements` SET `value` = ? WHERE `id` = ?';

        $configElement = $this->connection->query($selectSql)->fetch();

        if (!\is_array($configElement)) {
            return;
        }

        $serializedValue = $configElement['value'];
        if (!\is_string($serializedValue)) {
            return;
        }

        $unserializedValue = unserialize($serializedValue);
        if (!\is_string($unserializedValue)) {
            return;
        }

        $explodedValue = explode(';', $unserializedValue);
        if (!\is_array($explodedValue)) {
            return;
        }

        foreach ($explodedValue as $index => $singleValue) {
            if ($singleValue === 'motor') {
                unset($explodedValue[$index]);
            }
        }

        $newUnserializedValue = implode(';', $explodedValue);

        $newSerializedValue = serialize($newUnserializedValue);

        $this->connection->prepare($updateSql)->execute([$newSerializedValue, $configElement['id']]);
    }
}
