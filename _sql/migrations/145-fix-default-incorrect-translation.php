<?php
class Migrations_Migration145 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        UPDATE `s_core_snippets` SET `value` = 'Your e-mail address*:' WHERE name = 'sNewsletterLabelMail' AND `value` = 'Your e-mail addresse*:';
EOD;
        $this->addSql($sql);
    }
}
