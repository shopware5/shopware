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

namespace Shopware\Tests\Mink\Page\Helper\Elements;

use Behat\Mink\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

/**
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends Element implements HelperSelectorInterface
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.top-bar--language select.language--select'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'languages' => 'option',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors(): array
    {
        return [];
    }

    /**
     * Returns the current language
     * Use this only for asserts. If you only need the current language, use Helper::getCurrentLanguage().
     */
    public function getCurrentLanguage(): string
    {
        $languageKeys = [1 => 'de', 2 => 'en'];

        $selector = Helper::getRequiredSelector($this, 'languages');

        foreach ($this->findAll('css', $selector) as $language) {
            if ($language->getAttribute('selected')) {
                return $languageKeys[$language->getAttribute('value')];
            }
        }

        return 'de';
    }

    /**
     * Changes the language
     *
     * @throws ElementNotFoundException
     */
    public function setLanguage(string $language): void
    {
        $this->selectOption($language);
        Helper::setCurrentLanguage($language);
    }
}
