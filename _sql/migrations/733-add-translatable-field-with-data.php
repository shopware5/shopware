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

class Migrations_Migration733 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $translatables = [
            ['name' => 'article_slider_title',      'componentID' => 11],
            ['name' => 'title',                     'componentID' => 3],
            ['name' => 'link',                      'componentID' => 3],
            ['name' => 'banner_slider_title',       'componentID' => 7],
            ['name' => 'javascript',                'componentID' => 13],
            ['name' => 'smarty',                    'componentID' => 13],
            ['name' => 'manufacturer_slider_title', 'componentID' => 10],
            ['name' => 'iframe_url',                'componentID' => 9],
            ['name' => 'cms_title',                 'componentID' => 2],
            ['name' => 'text',                      'componentID' => 2],
            ['name' => 'video_id',                  'componentID' => 8],
        ];

        $sql = 'ALTER TABLE `s_library_component_field` ADD translatable INT(1) NOT NULL DEFAULT 0 AFTER `allow_blank`';
        $statement = $this->connection->prepare($sql);
        $statement->execute();

        $sql = <<<EOD
UPDATE `s_library_component_field`
    SET `translatable` = 1
    WHERE `name` = :name
    AND `componentID` = :componentID
EOD;
        $statement = $this->connection->prepare($sql);

        foreach ($translatables as $translatable) {
            $statement->execute([
                ':name' => $translatable['name'],
                ':componentID' => $translatable['componentID'],
            ]);
        }
    }
}
