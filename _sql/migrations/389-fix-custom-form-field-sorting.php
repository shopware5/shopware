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

class Migrations_Migration389 extends Shopware\Components\Migrations\AbstractMigration
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
                $fieldCountArray = $statement->fetch(PDO::FETCH_NUM);
                $fieldCount = array_shift($fieldCountArray);
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
