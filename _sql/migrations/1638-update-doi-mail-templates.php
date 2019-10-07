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
class Migrations_Migration1638 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // German mail text for sOPTINREGISTER
        $sql = <<<'EOD'
UPDATE `s_core_config_mails` SET `content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$salutation|salutation} {$lastname},

vielen Dank für Ihre Anmeldung bei {$sShop}.
Bitte bestätigen Sie die Registrierung über den nachfolgenden Link:

{$sConfirmLink}

Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.

{include file="string:{config name=emailfooterplain}"}', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$salutation|salutation} {$lastname},<br/>
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
`context` = 'a:14:{s:5:"sMAIL";s:14:"xy@example.org";s:7:"sConfig";a:0:{}s:6:"street";s:15:"Musterstraße 1";s:7:"zipcode";s:5:"12345";s:4:"city";s:11:"Musterstadt";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:4:"Herr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:11:"accountmode";s:1:"0";s:5:"email";s:14:"xy@example.org";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name` = 'sOPTINREGISTER' AND `dirty` = 0 AND `content` LIKE '%Bitte bestätigen Sie die Registrierung über den nachfolgenden Link%';
EOD;
        $this->addSql($sql);

        // German mail text for sOPTINREGISTERACCOUNTLESS
        $sql = <<<'EOD'
UPDATE `s_core_config_mails` SET `content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$salutation|salutation} {$lastname},

Bitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link:

{$sConfirmLink}

Nach der Bestätigung werden Sie in den Bestellabschluss geleitet, dort können Sie Ihre Bestellung nochmals überprüfen und abschließen.
Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.

{include file="string:{config name=emailfooterplain}"}', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$salutation|salutation} {$lastname},<br/>
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
`context` = 'a:14:{s:5:"sMAIL";s:14:"xy@example.org";s:7:"sConfig";a:0:{}s:6:"street";s:15:"Musterstraße 1";s:7:"zipcode";s:5:"12345";s:4:"city";s:11:"Musterstadt";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:4:"Herr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:11:"accountmode";s:1:"0";s:5:"email";s:14:"xy@example.org";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name` = 'sOPTINREGISTERACCOUNTLESS' AND `dirty` = 0 AND `content` LIKE '%Bitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link%';
EOD;
        $this->addSql($sql);

        // English mail text for sOPTINREGISTER
        $sql = <<<'EOD'
UPDATE `s_core_config_mails` SET `content`='{include file="string:{config name=emailheaderplain}"}

Hello {$salutation|salutation} {$lastname},

thank you for signing up at {$sShop}.
Please confirm your registration by clicking the following link:

{$sConfirmLink}

With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}', `contentHTML`='<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$salutation|salutation} {$lastname},<br/>
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
`context` = 'a:14:{s:7:"sConfig";a:0:{}s:5:"sMAIL";s:12:"xyz@mail.com";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:2:"Mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:11:"accountmode";s:1:"0";s:5:"email";s:12:"xyz@mail.com";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name` = 'sOPTINREGISTER' AND `dirty` = 0 AND `content` LIKE '%Please confirm your registration by clicking the following link%';
EOD;
        $this->addSql($sql);

        // English mail text for sOPTINREGISTERACCOUNTLESS
        $sql = <<<'EOD'
UPDATE `s_core_config_mails` SET `content`='{include file="string:{config name=emailheaderplain}"}

Hello {$salutation|salutation} {$lastname},

Please confirm your e-mail address using the following link:

{$sConfirmLink}

After the confirmation you will be directed your order overview, where you can check your order again and complete it.
With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}', `contentHTML`='<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$salutation|salutation} {$lastname},<br/>
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
`context` = 'a:14:{s:7:"sConfig";a:0:{}s:5:"sMAIL";s:12:"xyz@mail.com";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:2:"Mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:11:"accountmode";s:1:"0";s:5:"email";s:12:"xyz@mail.com";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name` = 'sOPTINREGISTERACCOUNTLESS' AND `dirty` = 0 AND `content` LIKE '%Please confirm your e-mail address using the following link%';
EOD;
        $this->addSql($sql);
    }
}
