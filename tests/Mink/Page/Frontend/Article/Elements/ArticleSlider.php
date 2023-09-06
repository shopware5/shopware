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

namespace Shopware\Tests\Mink\Page\Frontend\Article\Elements;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\SliderElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: ArticleSlider
 * Location: Emotion element for product sliders
 *
 * Available retrievable properties (per slide):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class ArticleSlider extends SliderElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.emotion--product-slider'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'slide' => '.product--box',
            'slideImage' => '.product--image img',
            'slideLink' => '.product--image',
            'slideName' => '.product--title',
            'slidePrice' => '.product--price',
        ];
    }

    /**
     * Returns the image source path
     *
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * Returns the link
     *
     * @return string
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selectors = Helper::getRequiredSelectors($this, ['slideLink', 'slideName']);

        $links = [
            'slideLink' => $slide->find('css', $selectors['slideLink'])->getAttribute('href'),
            'nameLink' => $slide->find('css', $selectors['slideName'])->getAttribute('href'),
        ];

        return Helper::getUnique($links);
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = Helper::getRequiredSelectors($this, ['slideImage', 'slideLink', 'slideName']);
        $nameElement = $slide->find('css', $selectors['slideName']);

        $names = [
            'linkTitle' => $slide->find('css', $selectors['slideLink'])->getAttribute('title'),
            'name' => trim($nameElement->getHtml()),
            'nameTitle' => $nameElement->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }

    /**
     * Returns the price
     */
    public function getPriceProperty(NodeElement $slide): float
    {
        $selector = Helper::getRequiredSelector($this, 'slidePrice');
        preg_match('(\d+,\d{2})', $slide->find('css', $selector)->getHtml(), $price);

        $currentPrice = current($price);
        if ($currentPrice === false) {
            return 0.0;
        }

        return Helper::floatValue($currentPrice);
    }
}
