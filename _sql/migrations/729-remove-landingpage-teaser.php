<?php

class Migrations_Migration729 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_emotion` DROP `landingpage_block`, DROP `landingpage_teaser`");
    }
}
