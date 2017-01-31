<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration919 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $this->addSql('UPDATE s_core_rewrite_urls SET org_path = REPLACE(org_path, "sViewport=ticket&sFid=", "sViewport=forms&sFid=") WHERE org_path LIKE "sViewport=ticket&sFid=%"');
    }
}
