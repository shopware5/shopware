<?php

class Migrations_Migration722 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        require_once __DIR__ . '/../../engine/Shopware/Bundle/AttributeBundle/Service/MigrationHelper.php';

        $helper = new \Shopware\Bundle\AttributeBundle\Service\MigrationHelper($this->connection);
        $helper->migrateAttributes('s_user_shippingaddress_attributes', 'shippingID');
    }
}
