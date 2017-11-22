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
use Shopware\Components\Migrations\AbstractMigration;
class Migrations_Migration962 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->addSql("SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Auth' LIMIT 1);");

        $sql = "INSERT INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options)
                    VALUES (@parent, 'price_precision', 's:1:\"2\";', 'Pflegbare Nachkommastellen in der Preismaske', 'Hier kannst du einstellen, wie viele Nachkommastellen du in der Artikelpreis-Make pflegen möchtest', 'text', '0', '0', '0', NULL);";

        $this->addSql($sql);
        $this->addSql("SET @elementID = (SELECT id FROM s_core_config_elements WHERE name = 'price_precision' LIMIT 1);");
        $this->addSql("INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES(@elementID, 2, 'Decimal precision of article prices in the backend', 'define a decimal precision for article prices in the backend');");
    }
}
