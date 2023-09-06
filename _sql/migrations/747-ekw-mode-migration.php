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

class Migrations_Migration747 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $statement = $this->connection->query('SELECT * FROM s_emotion_attributes LIMIT 1');
        $attributes = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($attributes)) {
            return;
        }

        if (!\array_key_exists('swag_mode', $attributes)) {
            return;
        }

        $sql = <<<EOD
UPDATE s_emotion AS emotion
INNER JOIN s_emotion_attributes AS attributes
    ON emotion.id = attributes.emotionID
SET emotion.mode = attributes.swag_mode
WHERE attributes.swag_mode = 'storytelling'
EOD;

        $this->addSql($sql);
    }
}
