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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket4375 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Checks password confirmation field
     */
    public function testPasswordChangeShouldFail()
    {
        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');

        if (is_array($this->View()->sErrorMessages)) {
            $this->fail("Login failed: ".array_pop($this->View()->sErrorMessages));
        }
        $this->reset();
        $this->Request()
            ->setMethod('POST')
            ->setPost('passwordConfirmation', 'shopware')
            ->setPost('password', 'shopware')
            ->setPost('currentPassword', '');
        $this->dispatch('/account/saveAccount');

        $this->assertInternalType('array', $this->View()->sErrorMessages);
        $this->assertContains('Das aktuelle Passwort stimmt nicht!', $this->View()->sErrorMessages);

        $this->reset();
    }
}
