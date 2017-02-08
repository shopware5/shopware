<?php
namespace Shopware\Tests\Mink\Page;

use Behat\Mink\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Backend extends Page
{
    const TIMEOUT_MILLISECONDS = 500;

    /**
     * @var string $path
     */
    protected $path = '/backend/';

    public function verifyLogin()
    {
        $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, 'document.querySelector(".login-window") !== null');

        $loginFormPresent = (
            $this->hasField('username') &&
            $this->hasField('password')
        );

        if (!$loginFormPresent) {
            throw new \Exception("Login form not there");
        }

        return true;
    }

    public function login($username, $password)
    {
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');

        return true;
    }

    public function verifyIsLoggedIn()
    {
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, 'document.querySelector(".shopware-menu") !== null');
        if (!$result) {
            throw new \Exception("Could not login");
        }

        return true;
    }

    /**
     * @param string $moduleName
     */
    public function openModule($moduleName)
    {
        $name = "Shopware.apps." . $moduleName;
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
            throw new \Exception("No Module was opened");
        }

        $selector = "document.querySelector('.x-window-header-text').innerHTML != 'Shopware Fehler Reporter'";
        $result = $this->getSession()->wait(self::TIMEOUT_MILLISECONDS, $selector);
        if (!$result) {
            throw new \Exception("Error Module was opened");
        }
    }
}
