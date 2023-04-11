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

namespace Shopware\Tests\Mink\Page\Backend\Backend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

class Backend extends Page
{
    public const TIMEOUT_MILLISECONDS = 500;

    /**
     * @var string
     */
    protected $path = '/backend/';

    /**
     * @return array<string, string|array>
     */
    public function getXPathSelectors(): array
    {
        return [
            'loginUsernameInput' => "//input[@name='username']",
            'loginUsernamepassword' => "//input[@name='password']",
            'loginLoginButton' => "//button[@data-action='login']",
        ];
    }

    public function login(string $user, string $password): void
    {
        $xpath = $this->getXPathSelectors();
        $this->find('xpath', $xpath['loginUsernameInput'])->setValue($user);
        $this->find('xpath', $xpath['loginUsernamepassword'])->setValue($password);
        $this->find('xpath', $xpath['loginLoginButton'])->click();
    }

    public function openModule(string $moduleName): void
    {
        $name = 'Shopware.apps.' . $moduleName;
        $this->getSession()->evaluateScript("openNewModule('$name');");
    }

    public function verifyModule(): void
    {
        $selector = "document.querySelector('.x-window-header-text') !== null";
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, $selector);
        if (!$result) {
            Helper::throwException('No Module was opened');
        }

        $selector = "document.querySelector('.x-window-header-text').innerHTML != 'Shopware Fehler Reporter'";
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, $selector);
        if (!$result) {
            Helper::throwException('Error Module was opened');
        }
    }
}
