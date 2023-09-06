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

namespace Shopware\Tests\Mink\Page\Backend\ContentTypeManager\Elements;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class ContentTypeManagerDetailWindow extends Element
{
    /**
     * @var string
     */
    protected $selector = '.x-window[id^="content-type-manager-detail-window"]';

    /**
     * @var array<string, array|string>
     */
    protected $elements = [
        'Allgemein' => ['xpath' => "//button[normalize-space()='Allgemein']"],
        'Felder' => ['xpath' => "//button[normalize-space()='Felder']"],
    ];

    public function getAddFieldButton(): NodeElement
    {
        return $this->getElement(AddFieldButton::class);
    }

    /**
     * @template TElement of Element
     *
     * @param class-string<TElement> $name
     *
     * @return TElement
     */
    public function getTab(string $name): Element
    {
        return $this->getElement($name);
    }

    public function getSaveButton(): NodeElement
    {
        return $this->getElement(SaveButton::class);
    }
}
