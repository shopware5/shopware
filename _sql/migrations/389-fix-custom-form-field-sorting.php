<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration389 extends AbstractMigration
{
    public function up($modus)
    {
        // Check if the table from the plugin is available
        try {
            $statement = $this->connection->query('SELECT DISTINCT id FROM s_cms_support;');
            $forms = $statement->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return;
        }

        foreach ($forms as $formId) {
            try {
                $statement = $this->connection->query("SELECT count(DISTINCT id) FROM s_cms_support_fields WHERE position = 0 AND supportID = $formId;");
                $fieldCount = (int) $statement->fetchColumn();
            } catch (Exception $e) {
                continue;
            }

            if ($fieldCount > 1) {
                $sql = <<<EOD
            SET @position:=0;
            UPDATE s_cms_support_fields SET position = @position:=@position+1 WHERE supportID = $formId;
EOD;
                $this->addSql($sql);
            }
        }
    }
}
