<?php

class Migrations_Migration786 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
    ALTER TABLE `s_crontab` ADD COLUMN `disable_on_error` TINYINT(1) NOT NULL DEFAULT 1 AFTER `active`;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
    INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`)
    VALUES
        ('backend/config/view/cron_job', 1, 1, 'detail/disable_on_error_label', 'Bei Fehler deaktivieren', NOW(), NOW()),
        ('backend/config/view/cron_job', 1, 2, 'detail/disable_on_error_label', 'Disable on error', NOW(), NOW())
    ;
EOD;
        $this->addSql($sql);
    }
}
