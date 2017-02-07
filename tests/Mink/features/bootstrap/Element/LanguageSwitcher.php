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
 * Element: LanguageSwitcher
 * Location: Language switcher on top of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class LanguageSwitcher extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.top-bar--language select.language--select'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'languages' => 'option',
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
     * Returns the current language
     * Use this only for asserts. If you only need the current language, use Helper::getCurrentLanguage().
     *
     * @return string
     */
    public function getCurrentLanguage()
    {
        $languageKeys = [1 => 'de', 2 => 'en'];

        $languages = $this->findAll('css', Helper::getRequiredSelector($this, 'languages'));

        /** @var Element $language */
        foreach ($languages as $language) {
            if ($language->getAttribute('selected')) {
                return $languageKeys[$language->getAttribute('value')];
            }
        }

        return 'de';
    }

    /**
     * Changes the language
     *
     * @param string $language
     *
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function setLanguage($language)
    {
        $this->selectOption($language);
        Helper::setCurrentLanguage($language);
    }
}
