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

class Migrations_Migration1625 extends Shopware\Components\Migrations\AbstractMigration
{
    private const LANGUAGE_GERMAN = 'de';
    private const LANGUAGE_ENGLISH = 'en';

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
            'label' => 'Artikelnummer',
            'active' => 0,
            'category' => 1,
            'position' => 5,
            'sortings' => json_encode([Shopware\Bundle\SearchBundle\Sorting\ProductNumberSorting::class => ['direction' => 'ASC']]),
        ];

        // Those definitely need to make use of the translation system in the future to prevent this conditions
        if ($modus !== static::MODUS_INSTALL && $this->getDefaultLocale() !== static::LANGUAGE_GERMAN) {
            $data['label'] = 'Product number';
        }

        $statement->execute($data);
    }

    private function getDefaultLocale()
    {
        $locale = $this->getConnection()->query(
            'SELECT SUBSTRING(loc.`locale`, 1, 2) AS locale FROM `s_core_shops` s JOIN `s_core_locales` loc ON loc.`id` = s.`locale_id` WHERE s.`default` = 1 LIMIT 1;'
        )->fetchColumn();

        return $locale === static::LANGUAGE_GERMAN ? static::LANGUAGE_GERMAN : static::LANGUAGE_ENGLISH;
    }
}
