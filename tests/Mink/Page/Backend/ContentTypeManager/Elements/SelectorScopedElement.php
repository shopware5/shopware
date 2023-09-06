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

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

class SelectorScopedElement extends Element
{
    public function __construct(Session $session, Factory $factory)
    {
        parent::__construct($session, $factory);

        $parentSelector = $this->selector;

        $this->elements = array_map(function ($childSelector) use ($parentSelector): string {
            $childType = \is_array($childSelector) ? array_key_first($childSelector) : 'css';
            $parentType = \is_array($parentSelector) ? array_key_first($parentSelector) : 'css';

            if ($childType !== $parentType) {
                Helper::throwException(sprintf("Combining the parent element's %s selector with the child's %s selector won't work.", $parentType, $childType));
            }

            return implode(
                $childType === 'css' ? ' ' : '//',
                [
                    \is_array($parentSelector) ? $parentSelector[$parentType] : $parentSelector,
                    \is_array($childSelector) ? $childSelector[$childType] : $childSelector,
                ]
            );
        }, $this->elements);
    }
}
