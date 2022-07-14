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

class Migrations_Migration740 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $components = [
            'emotion-components-article',
            'emotion-components-article-slider',
            'emotion-components-manufacturer-slider',
        ];

        $sql = <<<'EOD'
INSERT IGNORE INTO s_library_component_field
(componentID, name, x_type, allow_blank, position)
SELECT
    id as componentID,
    'no_border' as name,
    'checkbox' as x_type,
    1 as allow_blank,
    90 as position
FROM s_library_component
WHERE x_type = :xtype
EOD;

        $statement = $this->connection->prepare($sql);

        foreach ($components as $component) {
            $statement->execute([':xtype' => $component]);
        }
    }
}
