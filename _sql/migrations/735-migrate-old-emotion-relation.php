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

class Migrations_Migration735 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $statement = $this->connection->query('SELECT id, category_id FROM s_core_shops');
        $shopCategories = $statement->fetchAll(PDO::FETCH_KEY_PAIR);

        $sql = <<<EOD
INSERT IGNORE INTO s_emotion_shops (shop_id, emotion_id)
SELECT :shopId as shop_id,
       ec.emotion_id as emotion_id
FROM s_emotion_categories ec
    INNER JOIN s_emotion e
        ON e.id = ec.emotion_id
        AND e.is_landingpage = 1
    INNER JOIN s_categories c
        ON c.id = ec.category_id
        AND (c.path LIKE :path OR c.id = :categoryId)

EOD;

        $statement = $this->connection->prepare($sql);

        foreach ($shopCategories as $shopId => $category) {
            $path = '%|' . $category . '|%';
            $statement->execute([':path' => $path, ':categoryId' => $category, ':shopId' => $shopId]);
        }
    }
}
