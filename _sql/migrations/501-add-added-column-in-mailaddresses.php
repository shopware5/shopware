<?php

class Migrations_Migration501 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            ALTER TABLE `s_campaigns_mailaddresses`
            ADD `added` DATETIME DEFAULT NULL;
EOD;
        $this->addSql($sql);

        //set default value to current_timestamp this can't be done directly because this is only supported in mysql 5.6
        $sql = <<<'EOD'
            UPDATE s_campaigns_mailaddresses
            SET added = CURRENT_TIMESTAMP();
EOD;
        $this->addSql($sql);


        $sql = "UPDATE s_campaigns_mailaddresses ca
SET added = (SELECT cm.added FROM s_campaigns_maildata cm WHERE cm.email = ca.email LIMIT 1)";
        $this->addSql($sql);
    }
}