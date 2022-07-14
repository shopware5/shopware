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

class Migrations_Migration719 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $this->createAddressAttributeTable();

        $tables = [
            's_order_billingaddress_attributes',
            's_order_shippingaddress_attributes',
            's_user_shippingaddress_attributes',
            's_user_billingaddress_attributes',
        ];

        foreach ($tables as $table) {
            $this->applyAttributeSchema($table);
        }
    }

    private function createAddressAttributeTable()
    {
        $sql = <<<SQL
CREATE TABLE `s_user_addresses_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_id` (`address_id`),
  CONSTRAINT `s_user_addresses_attributes_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `s_user_addresses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
        $this->connection->exec($sql);
    }

    /**
     * @param string $table
     *
     * @throws Exception
     */
    private function applyAttributeSchema($table)
    {
        require_once __DIR__ . '/common/MigrationHelper.php';
        $helper = new MigrationHelper($this->connection);

        $attributes = $helper->getList($table);

        foreach ($attributes as $attribute) {
            $helper->update('s_user_addresses_attributes', $attribute['name'], $attribute['type']);
        }
    }
}
