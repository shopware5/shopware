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

class Migrations_Migration902 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->removeMenuEntry();
        $this->removeAclPermissions();
    }

    private function removeMenuEntry()
    {
        $this->addSql("DELETE FROM s_core_menu WHERE name LIKE 'Import/Export' AND controller LIKE 'ImportExport' LIMIT 1");
    }

    private function removeAclPermissions()
    {
        $this->addSql("DELETE r, p FROM s_core_acl_resources r JOIN s_core_acl_privileges p ON p.resourceID = r.id WHERE r.name = 'importexport'");
    }
}
