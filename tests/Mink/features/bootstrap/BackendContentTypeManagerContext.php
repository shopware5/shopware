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

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Backend\ContentTypeManager;

class BackendContentTypeManagerContext extends SubContext
{
    private ContentTypeManager $page;

    public function __construct(ContentTypeManager $page)
    {
        $this->page = $page;
    }

    /**
     * @When I click on the button to add a new content type
     */
    public function iClickOnTheAddContentTypeButton(): void
    {
        $this->page->clickAddButton();
    }

    /**
     * @When I click on the button to add a new field
     */
    public function iClickOnTheAddFieldButton(): void
    {
        $this->page->clickAddFieldButton();
    }

    /**
     * @When I click on the button to save the field
     */
    public function iClickOnTheSaveFieldButton(): void
    {
        $this->page->clickSaveFieldButton();
    }

    /**
     * @When I click on the button to save the content type
     */
    public function iClickOnTheSaveContentTypeButton(): void
    {
        $this->page->clickSaveContentTypeButton();
    }

    /**
     * @When I switch to the :name tab
     */
    public function iSwitchToTheTab(string $name): void
    {
        $this->page->switchTab($name);
    }
}
