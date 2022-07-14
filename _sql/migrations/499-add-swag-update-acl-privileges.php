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

class Migrations_Migration499 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add new ACL resource 'swagupdate'
        $sql = <<<'EOD'
            INSERT IGNORE INTO s_core_acl_resources (name) VALUES ('swagupdate');
EOD;
        $this->addSql($sql);

        // Add new ACL resource 'swagupdate'
        $sql = <<<'EOD'
            SET @resourceId = (SELECT id FROM s_core_acl_resources WHERE name = 'swagupdate' LIMIT 1);
EOD;
        $this->addSql($sql);

        // Add new ACL privileges corresponding to the ACL resource 'swagupdate'
        $sql = <<<'EOD'
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES (@resourceId, 'read');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES (@resourceId, 'update');
            INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES (@resourceId, 'skipUpdate');
EOD;
        $this->addSql($sql);

        // Reference the 'theme' ACL resource to the menu entry
        $sql = <<<'EOD'
            UPDATE s_core_menu SET resourceID = @resourceId WHERE controller = 'SwagUpdate';
EOD;
        $this->addSql($sql);
    }
}
