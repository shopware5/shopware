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

class Migrations_Migration1713 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Create Password Reset Parent
        $sql = <<<'EOD'
            INSERT INTO s_core_config_forms(`parent_id`, `name`, `label`, `position`)
            VALUES('80', 'passwordReset', 'Passwort Zurücksetzen', '0')
EOD;
        $this->addSql($sql);

        // Create Captcha Option in Password Reset
        $sql = <<<'EOD'
            INSERT IGNORE s_core_config_elements
                (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUE
                (LAST_INSERT_ID(), 'passwordResetCaptcha', '%s', 'Captcha für das Zurücksetzen des Passworts', 'Wenn diese Option aktiv ist, wird das Formular zum Zurücksetzen des Passworts mit einem Captcha geschützt.',  'combo', 1, 0, 1, 'a:5:{s:8:"editable";b:0;s:10:"valueField";s:2:"id";s:12:"displayField";s:11:"displayname";s:13:"triggerAction";s:3:"all";s:5:"store";s:12:"base.Captcha";}');
EOD;
        $this->addSql(sprintf($sql, serialize('nocaptcha')));

        // Translation
        $sql = <<<'EOD'
            SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'passwordResetCaptcha' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_element_translations`
                (`element_id`, `locale_id`, `label`, `description`)
            VALUES
                (@elementId, '2', 'Use captcha for the password reset form', 'If this option is active, a captcha is used to protect the Password reset form.');
EOD;
        $this->addSql($sql);

        // Config Element Translation

        $sql = <<<'EOD'
        SET @form_id = (SELECT id FROM `s_core_config_forms` WHERE `name` LIKE 'passwordReset' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT INTO s_core_config_form_translations(form_id, locale_id, label)
            VALUES (@form_id, 2, 'Password Reset');
EOD;
        $this->addSql($sql);
    }
}
