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

class Migrations_Migration306 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->getConnection()->prepare(
            "SELECT * FROM s_core_plugins WHERE name = 'Cron' AND installation_date IS NOT NULL"
        );

        $statement->execute();
        $data = $statement->fetchAll();

        if (!empty($data)) {
            $sql = <<<'EOD'
                SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'CronSecurity' LIMIT 1);

                SET @cronSecureByAccountId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND `name` = 'cronSecureByAccount');

                UPDATE `s_core_config_elements`
                SET `description` = 'Es werden nur Anfragen von authentifizierten Backend Benutzern akzeptiert'
                WHERE id = @cronSecureByAccountId
                AND `description` = 'Es werden nur Anfragen von authentifizierten Administratoren akzeptieren';

                UPDATE `s_core_config_element_translations`
                SET `description` = 'If set, requests received from authenticated backend users will be accepted'
                WHERE element_id = @cronSecureByAccountId
                AND `description` = 'If set, requests received from authenticated admin users will be accepted';
EOD;

            $this->addSql($sql);
        }
    }
}
