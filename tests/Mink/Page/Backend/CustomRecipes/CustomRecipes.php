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

namespace Shopware\Tests\Mink\Page\Backend\CustomRecipes;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Backend\CustomRecipes\Elements\CustomRecipesListWindow;
use Throwable;

class CustomRecipes extends Page
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=CustomRecipes';

    public function clickAddButton(): void
    {
        $this->getCustomRecipesListWindow()->getAddButton()->press();
    }

    public function getCustomRecipesListWindow(): CustomRecipesListWindow
    {
        return $this->getElement(CustomRecipesListWindow::class);
    }

    protected function verifyPage(): void
    {
        $recipesWindowPresent = $this->waitFor(10, function (CustomRecipes $page): bool {
            try {
                $page->getCustomRecipesListWindow();

                return true;
            } catch (Throwable $e) {
                return false;
            }
        });

        if ($recipesWindowPresent) {
            return;
        }

        throw new ElementNotFoundException(sprintf("Couldn't find '%s' on the current page", CustomRecipesListWindow::class));
    }
}
