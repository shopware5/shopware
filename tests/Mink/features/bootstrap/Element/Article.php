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

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

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
class Article extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--product'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'name' => '.product--title',
            'link' => '.product--image',
            'price' => '.product--price',
        ];
    }

    /**
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $names = [
            $elements['name']->getText(),
            $elements['name']->getAttribute('title'),
            $elements['link']->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getAttribute('src');
    }

    /**
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['name', 'link']);

        $links = [
            $elements['name']->getAttribute('href'),
            $elements['link']->getAttribute('href'),
        ];

        return Helper::getUnique($links);
    }

    /**
     * @return float
     */
    public function getPriceProperty()
    {
        $elements = Helper::findElements($this, ['price']);

        return Helper::floatValue($elements['price']->getText());
    }
}
