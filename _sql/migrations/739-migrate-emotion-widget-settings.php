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

class Migrations_Migration739 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $viewports = ['xs', 's', 'm', 'l', 'xl'];

        $sql = <<<EOD
INSERT IGNORE INTO s_emotion_element_viewports (elementID, emotionID, alias, start_row, start_col, end_row, end_col, visible)
SELECT
    id as elementID,
    emotionID,
    :viewport,
    start_row,
    start_col,
    end_row,
    end_col,
    1 as visible
FROM s_emotion_element
EOD;

        $statement = $this->connection->prepare($sql);

        foreach ($viewports as $viewport) {
            $statement->execute([':viewport' => $viewport]);
        }
    }
}
