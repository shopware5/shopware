<?php

use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\Sorting\SearchRankingSorting;
use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration919 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $this->addSql('UPDATE s_core_menu SET pluginID = NULL WHERE controller = "PluginManager"');
    }
}
