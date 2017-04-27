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

namespace Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Backend extends Page
{
    const TIMEOUT_MILLISECONDS = 500;

    /**
     * @var string
     */
    protected $path = '/backend/';

    public function getXPathSelectors()
    {
        return [
            'loginUsernameInput' => "//input[@name='username']",
            'loginUsernamepassword' => "//input[@name='password']",
            'loginLoginButton' => "//button[@data-action='login']",
        ];
    }

    public function login($user, $password)
    {
        $xpath = $this->getXPathSelectors();
        $this->find('xpath', $xpath['loginUsernameInput'])->setValue($user);
        $this->find('xpath', $xpath['loginUsernamepassword'])->setValue($password);
        $this->find('xpath', $xpath['loginLoginButton'])->click();
    }

    /**
     * @param string $moduleName
     */
    public function openModule($moduleName)
    {
        $name = 'Shopware.apps.' . $moduleName;
        $this->getSession()->evaluateScript("openNewModule('$name');");
    }

    /**
     * @throws \Exception
     */
    public function verifyModule()
    {
        $selector = "document.querySelector('.x-window-header-text') !== null";
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, $selector);
        if (!$result) {
            throw new \Exception('No Module was opened');
        }

        $selector = "document.querySelector('.x-window-header-text').innerHTML != 'Shopware Fehler Reporter'";
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, $selector);
        if (!$result) {
            throw new \Exception('Error Module was opened');
        }
    }
}
