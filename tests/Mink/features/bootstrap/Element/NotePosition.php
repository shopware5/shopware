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
 * Element: NotePosition
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class NotePosition extends CartPosition implements HelperSelectorInterface
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.note--item'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'a.note--title',
            'supplier' => 'div.note--supplier',
            'number' => 'div.note--ordernumber',
            'thumbnailLink' => 'a.note--image-link',
            'thumbnailImage' => 'a.note--image-link > img',
            'price' => 'div.note--price',
            'detailLink' => 'a.note--title',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'remove' => ['de' => 'Löschen',       'en' => 'Delete'],
            'compare' => ['de' => 'Vergleichen',   'en' => 'Compare'],
        ];
    }

    /**
     * Returns the product name
     *
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'thumbnailImage', 'detailLink']);

        $names = [
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleDetailLinkTitle' => $elements['detailLink']->getAttribute('title')
        ];

        return Helper::getUnique($names);
    }

    /**
     * Returns the image source path
     *
     * @return string
     */
    public function getImageProperty()
    {
        $element = Helper::findElements($this, ['thumbnailImage']);

        return $element['thumbnailImage']->getAttribute('srcset');
    }

    /**
     * Returns the link to the product
     *
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'detailLink']);

        $names = [
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleDetailLink' => $elements['detailLink']->getAttribute('href'),
        ];

        return Helper::getUnique($names);
    }
}
