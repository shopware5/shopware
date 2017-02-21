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
