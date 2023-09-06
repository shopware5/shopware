<?php

declare(strict_types=1);
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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1723 extends AbstractMigration
{
    public function up($modus)
    {
        $this->removeMenuEntries();
        $this->removeConfigElement();
        $this->removeWidget();
    }

    private function removeMenuEntries(): void
    {
        $sql = <<<'SQL'
DELETE FROM `s_core_menu` WHERE `controller` = 'Benchmark'
SQL;
        $this->addSql($sql);
    }

    private function removeConfigElement(): void
    {
        $sql = <<<'SQL'
SET @elementId = (SELECT `id` FROM `s_core_config_elements` WHERE name = 'benchmarkTeaser');
DELETE FROM s_core_config_values WHERE element_id = @elementId;
DELETE FROM s_core_config_elements WHERE id = @elementId;
SQL;
        $this->addSql($sql);
    }

    private function removeWidget(): void
    {
        $sql = <<<'SQL'
DELETE FROM `s_core_widgets` WHERE `name` = 'swag-bi-base'
SQL;
        $this->addSql($sql);
    }
}
