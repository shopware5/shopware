<?php

class Migrations_Migration722 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $helper = new \Shopware\Bundle\AttributeBundle\Service\MigrationHelper($this->connection);
        $helper->migrateAttributes('s_user_shippingaddress_attributes', 'shippingID');
    }
}
