<?php
class Migrations_Migration452 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateProductMenu();
    }

    public function updateProductMenu()
    {
        /** Article */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'ico package_green article--main' WHERE `id` = 1;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-inbox--plus article--add-article' WHERE `id` = 2;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-ui-scroll-pane-list article--overview' WHERE `id` = 66;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-blue-folders-stack article--categories' WHERE `id` = 4;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-property-blue article--properties' WHERE `s_core_menu`.`id` = 72;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-truck article--manufacturers' WHERE `id` = 6;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-balloon article--ratings' WHERE `id` = 50;
EOD;
        $this->addSql($sql);

        /** Contents */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'ico2 note03 contents--main' WHERE `id` = 7;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-documents contents--shopsites' WHERE `id` = 15;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-application-blog contents--blog' WHERE `id` = 85;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-application-form contents--forms' WHERE `id` = 57;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-arrow-circle-double-135 contents--import-export' WHERE `id` = 46;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-inbox-image contents--media-manager' WHERE `id` = 64;
EOD;
        $this->addSql($sql);

        /** Customers */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'ico customer customers--main' WHERE `id` = 20;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-user--plus customers--add-customer' WHERE `id` = 75;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-ui-scroll-pane-detail customers--customer-list' WHERE `id` = 21;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-sticky-notes-pin customers--orders' WHERE `id` = 22;
EOD;
        $this->addSql($sql);

        /** Properties */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'ico2 wrench_screwdriver settings--main' WHERE `id` = 23;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-bin-full settings--performance' WHERE `id` = 29;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-edit-shade settings--performance--cache' WHERE `id` = 91;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-wrench-screwdriver settings--basic-settings' WHERE `id` = 110;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-blueprint settings--system-info' WHERE `id` = 63;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-user-silhouette settings--user-management' WHERE `id` = 25;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-cards-stack settings--logfile' WHERE `id` = 68;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-envelope--arrow settings--delivery-charges' WHERE `id` = 26;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-credit-cards settings--payment-methods' WHERE `id` = 27;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-mail--pencil settings--mail-presets' WHERE `id` = 28;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-funnel--exclamation settings--riskmanagement' WHERE `id` = 62;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-edit-shade settings--snippets' WHERE `id` = 107;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-application-icon-large settings--theme-manager' WHERE `id` = 119;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-application-block settings--plugin-manager' WHERE `id` = 120;
EOD;
        $this->addSql($sql);

        /** Marketing */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'ico2 chart_bar01 marketing--main' WHERE `id` = 30;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-chart marketing--analyses' WHERE `id` = 69;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-report-paper marketing--analyses--overview' WHERE `id` = 31;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-chart marketing--analyses--stats-charts' WHERE `id` = 32;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-chart-down-color marketing--analyses--abort-analyses' WHERE `id` = 59;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-mail-forward marketing--analyses--email-notification' WHERE `id` = 84;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-image-medium marketing--banner' WHERE `id` = 8;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-pin marketing--shopping-worlds' WHERE `id` = 9;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-star marketing--premium-items' WHERE `id` = 11;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-mail-open-image marketing--vouchers' WHERE `id` = 10;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-folder-export marketing--product-exports' WHERE `id` = 12;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-xfn-colleague marketing--partner-program' WHERE `id` = 56;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-paper-plane marketing--newsletters' WHERE `id` = 58;
EOD;
        $this->addSql($sql);

        /** Misc */
        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-lifebuoy misc--help' WHERE `id` = 114;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-balloons-box misc--help--board' WHERE `id` = 88;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-lifebuoy misc--help--online-help' WHERE `id` = 41;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-briefcase--arrow misc--send-feedback' WHERE `id` = 115;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-arrow-continue-090 misc--software-update' WHERE `id` = 118;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-keyboard-command misc--shortcuts' WHERE `id` = 109;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        UPDATE `s_core_menu` SET `class` = 'sprite-shopware-logo misc--about-shopware' WHERE `id` = 44;
EOD;
        $this->addSql($sql);
    }
}
