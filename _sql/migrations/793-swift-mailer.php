<?php

class Migrations_Migration793 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @formId = (SELECT id FROM `s_core_config_forms` WHERE name='Mail');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`label` = 'SMTP Port',
`description` =  NULL,
`type` = 'number',
`value` = 's:0:\"\";'
WHERE `name` = 'mailer_port';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`description` = NULL,
`type` = 'select',
`options` = 'a:1:{s:5:\"store\";a:3:{i:0;a:2:{i:0;s:0:\"\";i:1;s:22:\"keine Verschlüsselung\";}i:1;a:2:{i:0;s:3:\"ssl\";i:1;s:3:\"SSL\";}i:2;a:2:{i:0;s:3:\"tls\";i:1;s:3:\"TLS\";}}}'
WHERE `name` = 'mailer_smtpsecure';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`value` = 's:5:\"login\";',
`description` = NULL,
`type` = 'select',
`options` = 'a:1:{s:5:\"store\";a:3:{i:0;a:2:{i:0;s:5:\"login\";i:1;s:5:\"login\";}i:1;a:2:{i:0;s:8:\"cram-md5\";i:1;s:8:\"cram-md5\";}i:2;a:2:{i:0;s:5:\"plain\";i:1;s:5:\"plain\";}}}'
WHERE `name` = 'mailer_auth';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`description` = NULL,
`type` = 'select',
`options` = 'a:1:{s:5:\"store\";a:4:{i:0;a:2:{i:0;s:4:\"mail\";i:1;s:3:\"PHP\";}i:1;a:2:{i:0;s:4:\"smtp\";i:1;s:4:\"SMTP\";}i:2;a:2:{i:0;s:5:\"gmail\";i:1;s:5:\"Gmail\";}i:3;a:2:{i:0;s:4:\"null\";i:1;s:22:\"NULL (nicht versenden)\";}}}'
WHERE `name` = 'mailer_mailer';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`label` = 'SMTP Host',
`description` = NULL
WHERE `name` = 'mailer_host';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET
`options` = 'a:1:{s:9:\"inputType\";s:8:\"password\";}'
WHERE `name` = 'mailer_password';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'mailer_delivery_address', 's:0:\"\";', 'Umleitungsadresse', 'Eine E-Mail-Adresse, an die ALLE E-Mails gesendet werden sollen. Es besteht die Möglichkeit, durch Semikolon getrennt, mehrere E-Mail-Adressen zu hinterlegen.', 'text', 0, 0, 1, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'mailer_delivery_whitelist', 's:0:\"\";', 'Ausnahmen von der Umleitung', 'Es erwartet ein PCRE-Suchmuster. Beispiel: /@shopware\\.com$|\\.shopware\\.com$/', 'text', 0, 0, 1, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'mailer_bcc_address', 's:0:\"\";', 'BCC-Adresse', 'Eine E-Mail-Adresse, an die alle E-Mails als Blindkopie gesendet werden soll.', 'text', 0, 0, 1, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'mailer_from_address', 's:0:\"\";', 'Absender-Adresse', 'Eine E-Mail-Adresse, die bei E-Mails ohne eigenen Absender genommen werden soll. Wenn keine hinterlegt ist, wird die Shopbetreiber-Adresse genommen.', 'text', 0, 0, 1, NULL),
(@formId, 'mailer_from_name', 's:0:\"\";', 'Absender-Name', 'Ein Name, welcher bei E-Mails ohne eigenen Absender genommen werden soll. Wenn keine hinterlegt ist, wird der Shop-Name verwendet.', 'text', 0, 0, 1, NULL),
(@formId, 'mailer_reply_to', 'b:0;', 'Antwort-Adresse setzen', 'Soll die Antwort-Adresse (Reply-To) automatisch gesetzt werden?', 'boolean', 0, 0, 1, NULL),
(@formId, 'mailer_reply_to_address', 's:0:\"\";', 'Antwort-Adresse', 'Eine E-Mail-Adresse, die bei E-Mails als Antwort-Adresse genommen werden soll. Wenn keine hinterlegt ist, wird die Absender-Adresse genommen.', 'text', 0, 0, 1, NULL),
(@formId, 'mailer_return_path', 's:0:\"\";', '„Wenn unzustellbar zurück an“', 'Welche Adresse soll als „Return-Path“ gesetzt werden?', 'text', 0, 0, 1, NULL);
EOD;
        $this->addSql($sql);
    }
}