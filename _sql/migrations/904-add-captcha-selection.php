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

class Migrations_Migration904 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->moveExistingCaptchaOptions();
        $this->createAdditionalCaptchaOptions($modus);
        $this->createAdditionalCaptchaOptionsTranslations();
    }

    private function moveExistingCaptchaOptions()
    {
        $this->addSql("SET @help_parent = (SELECT id FROM s_core_config_forms WHERE name='Frontend' LIMIT 1)");

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`) VALUES
(@help_parent , 'Captcha', 'Captcha', NULL, 0);
EOD;
        $this->addSql($sql);

        $this->addSql("SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Captcha' AND parent_id=@help_parent LIMIT 1)");
        $this->addSql("UPDATE s_core_config_elements SET form_id=@parent WHERE name='captchaColor'");
    }

    private function createAdditionalCaptchaOptionsTranslations()
    {
        $this->addSql("SET @captchaMethod = (SELECT id FROM s_core_config_elements WHERE name = 'captchaMethod' LIMIT 1)");
        $this->addSql("SET @noCaptchaAfterLogin = (SELECT id FROM s_core_config_elements WHERE name = 'noCaptchaAfterLogin' LIMIT 1)");

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES
(@captchaMethod, '2', 'Captcha Method', 'Choose the method to protect the forms against spam bots.'),
(@noCaptchaAfterLogin, '2', 'Disable after login', 'If set to yes, captchas are disabled for logged in customers');
EOD;
        $this->addSql($sql);
    }

    /**
     * @param string $modus
     */
    private function createAdditionalCaptchaOptions($modus)
    {
        $captchaMethod = 'default';
        if ($modus === self::MODUS_UPDATE) {
            $captchaMethod = 'legacy';
        }

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements`
(`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
(@parent, 'captchaMethod', '%s', 'Captcha Methode', 'Wählen Sie hier eine Methode aus, wie die Formulare gegen Spam-Bots geschützt werden sollen', 'combo', 1, 0, 1, 'a:5:{s:8:"editable";b:0;s:10:"valueField";s:2:"id";s:12:"displayField";s:11:"displayname";s:13:"triggerAction";s:3:"all";s:5:"store";s:12:"base.Captcha";}'),
(@parent, 'noCaptchaAfterLogin', 'b:0;', 'Nach Login ausblenden', 'Nach dem Login können Kunden Formulare ohne Captcha-Überprüfung absenden.', 'checkbox', 0, 1, 1, '');
EOD;
        $this->addSql(sprintf($sql, serialize($captchaMethod)));
    }
}
