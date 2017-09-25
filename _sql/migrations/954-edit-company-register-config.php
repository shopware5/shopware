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

class Migrations_Migration954 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            SET @element = (SELECT id FROM s_core_config_elements WHERE form_id = @parent AND name = 'showCompanySelectField' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE `s_core_config_elements` SET
            `description` = 'Wenn das Auswahlfeld nicht angezeigt wird, wird die Registrierung immer als Privatkunde durchgeführt. Das Auswahlfeld wird nur bei der Registrierung ausgeblendent, danach ist es beim Ändern der Benutzerdaten trotzdem verfügbar.'
            WHERE `id` = @element;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE `s_core_config_element_translations` SET
            `description` = 'If this option is false, all registrations will be done as a private customer. This option only affects the registration, it is still available when editing user data.'
            WHERE `element_id` = @element;
EOD;
        $this->addSql($sql);
    }
}
