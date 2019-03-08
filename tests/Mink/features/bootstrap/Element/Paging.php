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

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: Paging
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class Paging extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.listing--paging'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'previous' => 'a.paging--link.paging--prev',
            'next' => 'a.paging--link.paging--next',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @param string $direction
     * @param int    $steps
     */
    public function moveDirection($direction, $steps = 1)
    {
        $locator = [strtolower($direction)];
        $elements = Helper::findElements($this, $locator);

        for ($i = 0; $i < $steps; ++$i) {
            $result = Helper::countElements($this, $direction, 4);

            if ($result !== true) {
                Helper::throwException(
                    [sprintf('There is no more "%s" button! (after %d steps)', $direction, $i)]
                );
            }

            $elements[$direction]->click();
        }
    }

    /**
     * @param int $page
     */
    public function moveToPage($page)
    {
        while (!$this->hasLink($page)) {
            if ($this->noElement('next', false)) {
                $message = sprintf('Page %d was not found!', $page);
                Helper::throwException($message);

                return;
            }
            $this->moveDirection('next');
        }

        $this->clickLink($page);
    }

    /**
     * @param string $locator
     * @param bool   $throwException
     *
     * @return bool
     */
    public function noElement($locator, $throwException = true)
    {
        if (Helper::getRequiredSelector($this, $locator)) { //previous or next
            $result = Helper::countElements($this, $locator);
        } else { //page number (1, 2, 3, 4, ...)
            $result = !$this->hasLink($locator);
        }

        if ($result === true) {
            return true;
        }

        if ($throwException) {
            $message = sprintf('The Paging Link "%s" exists, but should not!', $locator);
            Helper::throwException($message);
        }

        return false;
    }
}
