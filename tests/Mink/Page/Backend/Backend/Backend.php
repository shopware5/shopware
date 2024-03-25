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

    public function login(string $user, string $password): void
    {
        $userNameInput = $this->find('xpath', "//input[@name='username']");
        if ($userNameInput === null) {
            Helper::throwException('No user name input element found');
        }
        $userNameInput->setValue($user);

        $passwordInput = $this->find('xpath', "//input[@name='password']");
        if ($passwordInput === null) {
            Helper::throwException('No password input element found');
        }
        $passwordInput->setValue($password);

        $loginButton = $this->find('xpath', "//button[@data-action='login']");
        if ($loginButton === null) {
            Helper::throwException('No login button found');
        }
        $loginButton->click();
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
