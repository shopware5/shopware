<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Page\Frontend\Homepage\Elements;

use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: CategoryTeaser
 * Location: Emotion element for category teasers
 *
 * Available retrievable properties:
 * - name (string, e.g. "Tees und Zubehör")
 * - image (string, e.g. "genuss_tees_banner.jpg")
 * - link (string, e.g. "/genusswelten/tees-und-zubehoer/")
 */
class CategoryTeaser extends MultipleElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.emotion--category-teaser'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'name' => '.category-teaser--title',
            'image' => 'style',
            'link' => '.category-teaser--link',
        ];
    }

    /**
     * Returns the category name
     *
     * @return array[]
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $names = [
            $elements['name']->getText(),
            $elements['link']->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }

    /**
     * Returns the category image
     *
     * @return array
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getHtml();
    }

    /**
     * Returns the category link
     *
     * @return array
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['link']);

        return $elements['link']->getAttribute('href');
    }
}
