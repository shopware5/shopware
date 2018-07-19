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
class Migrations_Migration1425 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * We need to rerun the Migrations from 5.4.5 <-> 5.4.6, to make the 5.5 beta 1 updatable
     *
     * @param string $modus
     */
    public function up($modus)
    {
        if ($this->connection->query('SELECT 1 FROM s_schema_version WHERE version = 1225')->fetchColumn()) {
            return;
        }

        // Get Privacy formId
        $this->addSql("SET @formId = ( SELECT id FROM `s_core_config_forms` WHERE name = 'Privacy' LIMIT 1 );");

        // Add Double-Opt-In optin in Backend
        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES ( @formId, 'optinregister', 'b:0;', 'Double-Opt-In für Registrierung', NULL, 'boolean', '0', '15', '0', NULL )";
        $this->addSql($sql);

        // Translation
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES ( LAST_INSERT_ID(), 2, 'Double opt in for registrations', NULL )";
        $this->addSql($sql);

        // Insert flags to show if a user is verified
        $sql = 'ALTER TABLE `s_user`
                ADD `doubleOptinConfirmDate`   datetime NULL          AFTER `paymentID`,
                ADD `doubleOptinEmailSentDate` datetime NULL          AFTER `paymentID`,
                ADD `doubleOptinRegister`      boolean  DEFAULT FALSE AFTER `paymentID`';
        $this->addSql($sql);

        // New Cronjob
        $sql = "INSERT INTO `s_crontab` (`name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `disable_on_error`, `end`, `inform_template`, `inform_mail`, `pluginID`) VALUES
                ('Lösche nicht aktivierte Benutzer', 'RegistrationCleanup', NULL, '', (CURDATE() + INTERVAL 3 HOUR), NULL, 86400, 1, 0, '2016-01-01 01:00:00', '', '', NULL);";
        $this->addSql($sql);

        // Add Cronjob-Settings in Backend
        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                VALUES ( @formId, 'optintimetodelete', 'i:3;', 'Tage ohne Verifizierung bis zur Löschung', 'Für Double-Opt-In: Zeitraum, nachdem nicht bestätigte Aktionen gelöscht werden.', 'number', '0', '16', '0', NULL )";
        $this->addSql($sql);

        // Translation
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES ( LAST_INSERT_ID(), 2, 'Days without confirmation until deletion', 'For Double-Opt-In: Time after which unconfirmed actions are deleted.' )";
        $this->addSql($sql);

        // Store localePrefix
        $sql =
            "SET @localePrefix = (
                SELECT MID(`locale`, 1, 2) AS localePrefix
                FROM `s_core_locales`
                WHERE `id` = (
                    SELECT locale_id
                    FROM `s_core_shops`
                    WHERE `default` = '1'
                    LIMIT 1
                )
                LIMIT 1
            );";
        $this->addSql($sql);

        // Add new Mailtemplate - German
        $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
SELECT
    'sOPTINREGISTER',
    '{config name=mail}',
    '{config name=shopName}',
    'Bitte bestätigen Sie Ihre Anmeldung bei {config name=shopName}',
    '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für Ihre Anmeldung bei {$sShop}.
Bitte bestätigen Sie die Registrierung über den nachfolgenden Link:

{$sConfirmLink}

Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für Ihre Anmeldung bei {$sShop}.<br/>
        Bitte bestätigen Sie die Registrierung über den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Anmeldung abschließen</a><br/>
        <br/>
        Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
FROM dual
WHERE @localePrefix = 'de'
EOD;
        $this->addSql($sql);

        // Add new Mailtemplate - English
        $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
SELECT
    'sOPTINREGISTER',
    '{config name=mail}',
    '{config name=shopName}',
    'Please confirm your registration at {config name=shopName}',
    '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for signing up at {$sShop}.
Please confirm your registration by clicking the following link:

{$sConfirmLink}

With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for signing up at {$sShop}.<br/>
        Please confirm your registration by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm registration</a><br/>
        <br/>
        With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
FROM dual
WHERE @localePrefix = 'en'
EOD;
        $this->addSql($sql);
    }
}
