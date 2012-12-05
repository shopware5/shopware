<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_IntegrationTests
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     S. Pohl
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_IntegrationTests
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license
 */
class Shopware_IntegrationTests_Backend_LoginTest extends Enlight_Components_Test_Selenium_TestCase
{
    /**
     * Username for the Backend Login
     *
     * @var string
     */
    public $loginUser = 'demo';

    /**
     * Passwort for the Backend Login
     *
     * @var string
     */
    public $loginPass = 'demo';

    /**
     * Helper method which logs in the selenium user
     *
     * Please notice that you'll need to call this method
     * in every test due to the fact that we aren't sharing
     * the session
     *
     * @return void
     */
    public function login()
    {
        $this->open('backend');

        $this->waitForElementPresent('css=.login-window');

        $this->type('css=.login-window input[name=username]', $this->loginUser);
        $this->type('css=.login-window input[name=password]', $this->loginPass);
        $this->click('css=.login-window button');

        $this->waitForElementPresent('css=.shopware-menu');
    }
}