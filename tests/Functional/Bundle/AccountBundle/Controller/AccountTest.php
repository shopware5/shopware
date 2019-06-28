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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Controller;

class AccountTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Test if the download goes through php
     *
     * @ticket SW-5226
     */
    public function testDownloadESDViaPhp()
    {
        $loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
        At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus
        est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam
        et justo duo dolores et ea rebum. Stet clita kasd gubergren,
        no sea takimata sanctus est Lorem ipsum dolor sit amet.';

        $filesystem = Shopware()->Container()->get('shopware.filesystem.private');
        $filePath = Shopware()->Config()->offsetGet('esdKey') . '/shopware_packshot_community_edition_72dpi_rgb.png';
        $deleteFolderOnTearDown = !$filesystem->has($filePath) ? $filePath : false;
        $filesystem->put($filePath, $loremIpsum);

        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        $this->reset();

        $params['esdID'] = 204;
        $this->Request()->setParams($params);

        $this->dispatch('/account/download');

        static::assertEquals('attachment; filename="shopware_packshot_community_edition_72dpi_rgb.png"', $this->Response()->getHeader('Content-Disposition'));
        static::assertGreaterThan(630, (int) $this->Response()->getHeader('Content-Length'));
        static::assertEquals(strlen($this->Response()->getBody()), (int) $this->Response()->getHeader('Content-Length'));

        if ($deleteFolderOnTearDown) {
            $filesystem->delete($filePath);
        }
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testNormalLogin()
    {
        static::assertEmpty(Shopware()->Session()->sUserId);
        $this->Request()->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        static::assertNotEmpty(Shopware()->Session()->sUserId);
        static::assertEquals(1, Shopware()->Session()->sUserId);

        $this->logoutUser();
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testHashPostLogin()
    {
        //test with md5 password and without the ignoreAccountMode parameter
        static::assertEmpty(Shopware()->Session()->sUserId);
        $this->setUserDataToPost();
        $this->dispatch('/account/login');
        static::assertEmpty(Shopware()->Session()->sUserId);

        $this->logoutUser();
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testWithoutIgnoreLogin()
    {
        //test the internal call of the method with the $ignoreAccountMode parameter

        $this->setUserDataToPost();
        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin(true);
        static::assertNotEmpty(Shopware()->Session()->sUserId);
        static::assertEquals(1, Shopware()->Session()->sUserId);
        static::assertEmpty($result['sErrorFlag']);
        static::assertEmpty($result['sErrorMessages']);

        $this->logoutUser();
        //test the internal call of the method without the $ignoreAccountMode parameter

        $this->setUserDataToPost();

        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin();
        static::assertEmpty(Shopware()->Session()->sUserId);
        static::assertNotEmpty($result['sErrorFlag']);
        static::assertNotEmpty($result['sErrorMessages']);
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testWithIgnoreLogin()
    {
        //test the internal call of the method without the $ignoreAccountMode parameter
        $this->setUserDataToPost();

        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin();
        static::assertEmpty(Shopware()->Session()->sUserId);
        static::assertNotEmpty($result['sErrorFlag']);
        static::assertNotEmpty($result['sErrorMessages']);

        $this->logoutUser();
    }

    /**
     * helper to logout the user
     */
    private function logoutUser()
    {
        //reset the request
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->dispatch('/account/logout');
        //reset the request
        $this->reset();
    }

    /**
     * set user data to post
     */
    private function setUserDataToPost()
    {
        $sql = 'SELECT email, password FROM s_user WHERE id = 1';
        $userData = Shopware()->Db()->fetchRow($sql);
        $this->Request()->setMethod('POST')
            ->setPost('email', $userData['email'])
            ->setPost('passwordMD5', $userData['password']);
    }
}
