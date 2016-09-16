<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration791 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
            UPDATE `s_core_subscribes`
            SET `subscribe` = 'Enlight_Controller_Action_PostDispatchSecure_Frontend'
            WHERE `listener` = 'Shopware_Plugins_Frontend_LastArticles_Bootstrap::onPostDispatch';
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            ALTER TABLE `s_emarketing_lastarticles` DROP `img`;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'LastArticles');

            UPDATE `s_core_config_elements`
            SET `scope` = '1'
            WHERE `form_id` = @formId
              AND (`name` = 'lastarticlestoshow' OR `name` = 'time');
SQL;
        $this->addSql($sql);
    }
}
