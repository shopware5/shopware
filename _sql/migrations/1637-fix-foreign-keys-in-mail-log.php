<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
class Migrations_Migration1637 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
ALTER TABLE s_mail_log DROP FOREIGN KEY s_mail_log_fk_type_id;
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_type_id
  FOREIGN KEY (type_id)
  REFERENCES s_core_config_mails (id) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log DROP FOREIGN KEY s_mail_log_fk_order_id;
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_order_id
  FOREIGN KEY (order_id)
  REFERENCES s_order (id) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log DROP FOREIGN KEY s_mail_log_fk_shop_id;
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_shop_id
  FOREIGN KEY (shop_id)
  REFERENCES s_core_shops (id) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $this->addSql($sql);
    }
}
