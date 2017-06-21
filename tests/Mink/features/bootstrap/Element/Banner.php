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
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: Banner
 * Location: Emotion element for image banners
 *
 * Available retrievable properties:
 * - image (string, e.g. "deli_teaser503886c2336e3.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - mapping (array[])
 */
class Banner extends MultipleElement implements HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--banner'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'image' => '.banner--image',
            'link' => '.banner--link',
            'mapping' => '.banner--mapping-link',
        ];
    }

    /**
     * Returns the banner image
     *
     * @return array
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getAttribute('src');
    }

    /**
     * Returns the banner link
     *
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['link']);

        return $elements['link']->getAttribute('href');
    }

    /**
     * Returns the banner mapping
     *
     * @return array[]
     */
    public function getMapping()
    {
        $elements = Helper::findAllOfElements($this, ['mapping']);
        $mapping = [];

        foreach ($elements['mapping'] as $link) {
            $mapping[] = ['mapping' => $link->getAttribute('href')];
        }

        return $mapping;
    }

    /**
     * Clicks the banner link
     *
     * @throws \Behat\Mink\Exception\ElementException
     */
    public function click()
    {
        $elements = Helper::findElements($this, ['link']);
        $elements['link']->click();
    }
}
