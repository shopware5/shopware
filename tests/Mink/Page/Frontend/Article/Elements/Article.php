<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Page\Frontend\Article\Elements;

use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: Article
 * Location: Emotion element for products
 *
 * Available retrievable properties:
 * - name (string, e.g. "All Natural - Lemon Honey Soap")
 * - text (string, e.g. "Ichilominus Fultus ordior, ora Sterilis qua Se sum cum Conspicio sed Eo at ver oportet, ..."
 * - price (float, e.g. "11,40 €")
 * - link (string, e.g. "/sommerwelten/beauty-und-care/218/all-natural-lemon-honey-soap")
 *
 * Currently not retrievable properties:
 * - image (string)
 */
class Article extends MultipleElement
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--product'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'name' => '.product--title',
            'link' => '.product--image',
            'price' => '.product--price',
        ];
    }

    public function getNameProperty(): string
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $names = [
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }

    public function getImageProperty(): string
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getAttribute('src');
    }

    public function getLinkProperty(): string
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $links = [
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href'),
        ];

        return Helper::getUnique($links);
    }

    public function getPriceProperty(): float
    {
        $elements = Helper::findElements($this, ['price']);

        return Helper::floatValue($elements['price']->getText());
    }
}
