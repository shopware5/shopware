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
class Migrations_Migration1407 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addConfigElementTranslation();
        if ($modus === self::MODUS_INSTALL) {
            $this->alterHolidays();
        }
        $this->correctTypoInHolidays();
    }

    private function addConfigElementTranslation()
    {
        $sql = <<<'EOD'
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'registerCaptcha' LIMIT 1);
EOD;
        $this->addSql($sql);
        $sql = <<<'SQL'
INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES
(@elementId, 2, 'Use captcha in registration', 'If active, a captcha will be shown in the registration. The recommended method for registrations is honeypot.');
SQL;
        $this->addSql($sql);
    }

    private function alterHolidays()
    {
        $sql = <<<'EOD'
DELETE FROM s_premium_holidays WHERE id = 2;
DELETE FROM s_premium_holidays WHERE id = 12;
EOD;

        $this->addSql($sql);
    }

    private function correctTypoInHolidays()
    {
        $sql = <<<'EOD'
UPDATE s_premium_holidays SET name = 'Silvester' WHERE name = 'Sylvester';
EOD;

        $this->addSql($sql);
    }
}
