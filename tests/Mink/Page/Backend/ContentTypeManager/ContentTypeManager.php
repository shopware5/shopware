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

namespace Shopware\Tests\Mink\Page\Backend\ContentTypeManager;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Backend\ContentTypeManager\Elements\ContentTypeManagerDetailWindow;
use Shopware\Tests\Mink\Page\Backend\ContentTypeManager\Elements\ContentTypeManagerFieldWindow;
use Shopware\Tests\Mink\Page\Backend\ContentTypeManager\Elements\ContentTypeManagerListWindow;
use Throwable;

class ContentTypeManager extends Page
{
    /**
     * @var string
     */
    protected $path = '/backend/?app=ContentTypeManager';

    public function clickAddButton(): void
    {
        $this->getListWindow()->getAddButton()->press();
    }

    public function clickAddFieldButton(): void
    {
        $this->clickUsingJs($this->getDetailWindow()->getAddFieldButton(), $this->getDetailWindow());
    }

    public function clickSaveFieldButton(): void
    {
        $this->getFieldWindow()->getSaveButton()->press();
    }

    public function clickSaveContentTypeButton(): void
    {
        $this->getDetailWindow()->getSaveButton()->press();
    }

    /**
     * @param class-string<Element> $name
     */
    public function switchTab(string $name): void
    {
        $this->getDetailWindow()->getTab($name)->click();
    }

    public function getListWindow(): ContentTypeManagerListWindow
    {
        return $this->getElement(ContentTypeManagerListWindow::class);
    }

    public function getDetailWindow(): ContentTypeManagerDetailWindow
    {
        return $this->getElement(ContentTypeManagerDetailWindow::class);
    }

    public function getFieldWindow(): ContentTypeManagerFieldWindow
    {
        return $this->getElement(ContentTypeManagerFieldWindow::class);
    }

    protected function verifyPage(): void
    {
        $listWindowPresent = $this->waitFor(10, function (ContentTypeManager $page): bool {
            try {
                $page->getListWindow();

                return true;
            } catch (Throwable $e) {
                return false;
            }
        });

        if ($listWindowPresent) {
            return;
        }

        throw new ElementNotFoundException(sprintf("Couldn't find '%s' on the current page", ContentTypeManagerListWindow::class));
    }

    private function clickUsingJs(NodeElement $el, ?NodeElement $contextNode = null): void
    {
        $script = <<<'JS'
var contextNodeXPath = "%s",
    contextNode = null;

if (contextNodeXPath !== '') {
    contextNode = document.evaluate(contextNodeXPath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
}

var btn = document.evaluate("%s", contextNode ?? document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;

btn.click();
JS;

        $this->getDriver()->executeScript(
            sprintf($script, $contextNode ? $contextNode->getXpath() : '', $el->getXpath()
            )
        );
    }
}
