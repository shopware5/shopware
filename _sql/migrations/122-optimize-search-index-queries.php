<?php
class Migrations_Migration122 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE  `s_search_index` ADD INDEX  `clean_up_index` (  `keywordID` ,  `fieldID` );
ALTER TABLE  `s_search_fields` ADD INDEX (  `tableID` );
EOD;

        $this->addSql($sql);
    }
}
