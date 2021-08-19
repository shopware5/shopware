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

namespace Shopware\Tests\Mink\Page\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\Backend\CustomRecipesListWindow;

class CustomRecipes extends Page
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=CustomRecipes';

    protected function verifyPage(): void
    {
        $recipesWindowPresent = $this->waitFor(10, function (CustomRecipes $page): bool {
            try {
                return $page->getCustomRecipesListWindow() instanceof Element;
            } catch (\Throwable $e) {
                return false;
            }
        });

        if ($recipesWindowPresent) {
            return;
        }

        throw new ElementNotFoundException(sprintf('Couldn\'t find "%s" on the current page', CustomRecipesListWindow::class));
    }

    public function clickAddButton(): void
    {
        $this->getCustomRecipesListWindow()->getAddButton()->press();
    }

    public function getCustomRecipesListWindow(): CustomRecipesListWindow
    {
        $window = $this->getElement(CustomRecipesListWindow::class);

        if ($window instanceof CustomRecipesListWindow) {
            return $window;
        }

        throw new ElementNotFoundException(sprintf('Couldn\'t find "%s" on the current page', CustomRecipesListWindow::class));
    }
}
