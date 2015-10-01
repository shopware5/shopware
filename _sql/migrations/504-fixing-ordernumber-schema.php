<?php

class Migrations_Migration504 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // fixing ordernumber for s_addon_premiums
        $sql = <<<'EOD'
            ALTER TABLE `s_addon_premiums`
            MODIFY COLUMN `ordernumber` varchar(40),
            MODIFY COLUMN `ordernumber_export` varchar(40);
EOD;
        $this->addSql($sql);

        // fixing ordernumber for s_articles_notification
        $sql = <<<'EOD'
            ALTER TABLE `s_articles_notification`
            MODIFY COLUMN `ordernumber` varchar(40);
EOD;
        $this->addSql($sql);

        // fixing ordernumber for s_campaigns_articles
        $sql = <<<'EOD'
            ALTER TABLE `s_campaigns_articles`
            MODIFY COLUMN `articleordernumber` varchar(40);
EOD;
        $this->addSql($sql);

        // fixing ordernumber for s_order_basket
        $sql = <<<'EOD'
            ALTER TABLE `s_order_basket`
            MODIFY COLUMN `ordernumber` varchar(40);
EOD;
        $this->addSql($sql);

        // fixing ordernumber for s_order_details
        $sql = <<<'EOD'
            ALTER TABLE `s_order_details`
            MODIFY COLUMN `articleordernumber` varchar(40);
EOD;
        $this->addSql($sql);

        // fixing ordernumber for s_order_notes
        $sql = <<<'EOD'
            ALTER TABLE `s_order_notes`
            MODIFY COLUMN `ordernumber` varchar(40);
EOD;
        $this->addSql($sql);
    }
}