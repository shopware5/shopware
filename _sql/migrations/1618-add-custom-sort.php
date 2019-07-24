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

class Migrations_Migration1618 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     */
    public function up($modus)
    {
        // Make sorting available
        $statement = $this->getConnection()->prepare(
            'INSERT INTO `s_search_custom_sorting` (`label`, `active`, `display_in_categories`, `position`, `sortings`)
                VALUES (:label, :active, :category, :position, :sortings)'
        );
        $data = [
            'label' => 'Position',
            'active' => $modus === self::MODUS_INSTALL ? 1 : 0,
            'category' => 1,
            'position' => 5,
            'sortings' => json_encode([Shopware\Bundle\SearchBundle\Sorting\ManualSorting::class => ['direction' => 'ASC']]),
        ];
        $statement->execute($data);


        $this->addSql('CREATE TABLE `s_categories_manual_sorting` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `position` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `category_id_product_id` (`category_id`, `product_id`)
)
COLLATE=\'utf8_unicode_ci\'
ENGINE=InnoDB
;
');
    }
}
