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

namespace Shopware\Tests\Mink\Page\Frontend\Note\Elements;

use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: NotePosition
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class NotePosition extends MultipleElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.note--item'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
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
    public function getNamedSelectors(): array
    {
        return [
            'remove' => ['de' => 'Löschen',       'en' => 'Delete'],
            'compare' => ['de' => 'Vergleichen',   'en' => 'Compare'],
        ];
    }

    /**
     * Returns the product name
     */
    public function getNameProperty(): string
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'thumbnailImage', 'detailLink']);

        $names = [
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleDetailLinkTitle' => $elements['detailLink']->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }

    /**
     * Returns the image source path
     */
    public function getImageProperty(): string
    {
        $element = Helper::findElements($this, ['thumbnailImage']);

        return $element['thumbnailImage']->getAttribute('srcset');
    }

    /**
     * Returns the link to the product
     */
    public function getLinkProperty(): string
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
