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

class Migrations_Migration1721 extends AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            SET @formId = (SELECT id FROM s_core_config_forms where name = 'Frontend33' LIMIT 1)
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE s_core_config_elements
                (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUE
                (@formId, 'shopSalutationRequired', 'b:1;', 'Anrede benötigt', 'Ob eine Anrede bei der Registrierung benötigt wird oder nicht.', 'boolean', 1, 1015, 1, '');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'shopSalutationRequired' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_element_translations`
                (`element_id`, `locale_id`, `label`, `description`)
            VALUES
                (@elementId, '2', 'Salutation required', 'Whether or not a salutation is required upon registration.');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            SET @salutation = (SELECT value FROM s_core_config_elements where name = 'shopsalutations' LIMIT 1)
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE s_core_config_elements SET value =
                (SELECT CONCAT('s:', CHAR_LENGTH(CONCAT(REPLACE(SUBSTRING(@salutation FROM 6), '";', ''), ',not_defined')), ':"', CONCAT(REPLACE(SUBSTRING(@salutation FROM 6), '";', ''), ',not_defined'), '";'))
                where name = 'shopsalutations';
EOD;
        $this->addSql($sql);

        $this->updateMailTemplates();
    }

    private function updateMailTemplates(): void
    {
        $sql = <<<'EOD'
        UPDATE s_core_config_mails SET
        content = REPLACE(content, '{$salutation|salutation}', '{$firstname}'),
        contentHTML = REPLACE(contentHTML, '{$salutation|salutation}', '{$firstname}')
        WHERE DIRTY = 0
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE s_core_config_mails SET
        content = REPLACE(content, '{$billingaddress.salutation|salutation}', '{$billingaddress.firstname}'),
        contentHTML = REPLACE(contentHTML, '{$billingaddress.salutation|salutation}', '{$billingaddress.firstname}')
        WHERE DIRTY = 0
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE s_core_config_mails SET
        content = REPLACE(content, '{$sUser.billing_salutation|salutation}', '{$sUser.billing_firstname}'),
        contentHTML = REPLACE(contentHTML, '{$sUser.billing_salutation|salutation}', '{$sUser.billing_firstname}')
        WHERE DIRTY = 0
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE s_core_config_mails SET
        content = REPLACE(content, '{$sUser.salutation|salutation}', '{$sUser.firstname}'),
        contentHTML = REPLACE(contentHTML, '{$sUser.billing_salutation|salutation}', '{$sUser.billing_firstname}')
        WHERE DIRTY = 0
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE s_core_config_mails SET
        content = REPLACE(content, '{$user.salutation|salutation}', '{$user.firstname}'),
        contentHTML = REPLACE(contentHTML, '{$user.salutation|salutation}', '{$user.firstname}')
        WHERE DIRTY = 0
EOD;
        $this->addSql($sql);
    }
}
