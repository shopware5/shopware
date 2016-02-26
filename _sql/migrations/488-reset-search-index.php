<?php
class Migrations_Migration488 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
Delete IGNORE FROM `s_search_index`;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
Delete IGNORE FROM `s_search_keywords`;
EOD;
        $this->addSql($sql);
    }
}
