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
class Migrations_Migration1229 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = "SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Privacy' LIMIT 1)";
        $this->addSql($sql);

        $sql = "SET @position = (SELECT position FROM s_core_config_elements WHERE name = 'optinregister' LIMIT 1)";
        $this->addSql($sql);

        $sql = "INSERT INTO s_core_config_elements
                (form_id, name, value, label, description, type, required, position)
                VALUES
                (@formId, 'optinaccountless', 'b:0;', 'Double-Opt-In für Schnellbesteller', NULL, 'boolean', 0, (@position + 1))";
        $this->addSql($sql);

        // Translation
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES ( LAST_INSERT_ID(), 2, 'Double opt in for quick orderer', NULL )";
        $this->addSql($sql);

        $sql = "UPDATE s_core_config_elements
                SET position = (@position + 2)
                WHERE name = 'optintimetodelete'";
        $this->addSql($sql);

        // Store localePrefix
        $sql = "SELECT MID(`locale`, 1, 2) AS localePrefix
                FROM `s_core_locales`
                WHERE `id` = (
                    SELECT locale_id
                    FROM `s_core_shops`
                    WHERE `default` = '1'
                    LIMIT 1
                )
                LIMIT 1
            ";
        $localePrefix = $this->connection->query($sql)->fetchColumn();

        // Add new Mailtemplate - German
        if (strtolower($localePrefix) === 'de') {
            $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
VALUES
(
    'sOPTINREGISTERACCOUNTLESS',
    '{config name=mail}',
    '{config name=shopName}',
    'Bitte bestätigen Sie Ihre E-Mail-Adresse für Ihre Bestellung bei {config name=shopName}',
    '{include file="string:{config name=emailheaderplain}"}
    
Hallo,

Bitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link:

{$sConfirmLink}

Nach der Bestätigung werden Sie in den Bestellabschluss geleitet, dort können Sie Ihre Bestellung nochmals überprüfen und abschließen.
Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        Bitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Bestellung fortsetzen</a><br/>
        <br/>
        Nach der Bestätigung werden Sie in den Bestellabschluss geleitet, dort können Sie Ihre Bestellung nochmals überprüfen und abschließen.<br/>
        Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
)
EOD;
            $this->addSql($sql);
        }
        // Add new Mailtemplate - English (FallbacK)
        else {
            $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
VALUES
(
    'sOPTINREGISTERACCOUNTLESS',
    '{config name=mail}',
    '{config name=shopName}',
    'Please confirm your e-mail address for your order at {config name=shopName}',
    '{include file="string:{config name=emailheaderplain}"}

Hello,

Please confirm your e-mail address using the following link:

{$sConfirmLink}

After the confirmation you will be directed your order overview, where you can check your order again and complete it.
With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        Please confirm your e-mail address using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm e-mail</a><br/>
        <br/>
        After the confirmation you will be directed your order overview, where you can check your order again and complete it.<br/>
        With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
)
EOD;
            $this->addSql($sql);
        }
    }
}
