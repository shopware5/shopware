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

class Migrations_Migration939 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateMenuEntry();
    }

    private function updateMenuEntry()
    {
        $sql = <<<SQL
SET @old = (SELECT id FROM s_core_menu
WHERE name = 'Import/Export' AND (controller = 'ImportExport' OR controller = 'PluginManager') AND active = 1 LIMIT 1);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
SET @new = (SELECT id FROM s_core_menu
WHERE name = 'Import/Export Advanced' AND controller = 'SwagImportExport' AND active = 1 LIMIT 1);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
DELETE FROM s_core_menu WHERE id = @new AND @old IS NOT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_menu SET controller = 'SwagImportExport', class = 'sprite-arrow-circle-double-135 contents--import-export'
WHERE id = @old AND @new IS NOT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_snippets SET value = 'Import/Export'
WHERE name = 'SwagImportExport' AND value = 'Import/Export Advanced' AND @old IS NOT NULL AND @new IS NOT NULL;
SQL;
        $this->addSql($sql);
    }
}
