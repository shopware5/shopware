<?php declare(strict_types=1);

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1650 extends AbstractMigration
{
    public function up($modus): void
    {
        $secret = bin2hex(random_bytes(32));
        $serializedSecret = serialize($secret);
        $escapedSerializedSecret = $this->getConnection()->quote($serializedSecret);

        $sql = <<<SQL
SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'Core');
INSERT IGNORE INTO `s_core_config_elements` (
    `form_id`,
    `name`,
    `value`,
    `label`,
    `description`,
    `type`,
    `required`,
    `position`,
    `scope`,
    `filters`,
    `validators`,
    `options`
)
VALUES (
    @formId,
    'ApplicationSecret',
    '$escapedSerializedSecret',
    'Anwendungsgeheimnis',
    'Wird genutzt um Daten für die Installation spezifisch zu beeinflussen.',
    'text',
    TRUE,
    0,
    0,
    NULL,
    NULL,
    ''
);
SET @localeID = (SELECT `id` FROM `s_core_locales` WHERE `locale` = 'en_GB');
SET @elementID = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'ApplicationSecret');
INSERT IGNORE INTO `s_core_config_element_translations` (
    `element_id`,
    `locale_id`,
    `label`,
    `description`
)
VALUES (
    @elementID,
    @localeID,
    'Application secret',
    'Is used to alter data specifically for this installation'
);
SQL;
        $this->addSql($sql);
    }
}
