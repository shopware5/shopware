<?php declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Privacy;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Services\CookieHandler;
use Shopware\Bundle\CookieBundle\Services\CookieRemoveHandler;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use Shopware\Components\Privacy\CookieRemoveSubscriber;

class CookieRemoveSubscriberTest extends TestCase
{
    public function testPostDispatchDoesNothingRemovalInactive(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', 0);
        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber();

        $controller = $this->getController();

        // May not be removed, since further code may not be executed
        $controller->Response()->setCookie('notRemoved', 'foo');

        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        // More code than allowed got executed if this fails
        static::assertNotEmpty($controller->Response()->getCookies());

        Shopware()->Config()->offsetSet('cookie_note_mode', 1);
        Shopware()->Config()->offsetSet('show_cookie_note', 0);
        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber();
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        // More code than allowed got executed if this fails
        static::assertNotEmpty($controller->Response()->getCookies());
    }

    public function testPostDispatchDoesNothingAllowAllModeCookieSet(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_ALL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $_COOKIE['allowCookie'] = 1;

        $controller = $this->getController();

        // May not be removed, since further code may not be executed
        $controller->Response()->setCookie('notRemoved', 'foo');

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber();
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        // More code than allowed got executed if this fails
        static::assertNotEmpty($controller->Response()->getCookies());
    }

    public function testPostDispatchAddsConfigHeaderHttpCacheEnabled(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_ALL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $controller = $this->getController();

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber();
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        static::assertNotNull($controller->Response()->headers->get(CookieRemoveHandler::COOKIE_CONFIG_KEY));
    }

    public function testPostDispatchDoesNotAddConfigHeaderHttpCacheDisabled(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_ALL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $controller = $this->getController();

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber(false);
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        static::assertNull($controller->Response()->headers->get(CookieRemoveHandler::COOKIE_CONFIG_KEY));
    }

    public function testPostDispatchAddsCookieCollectionHeaderHttpCacheEnabled(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_TECHNICAL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $controller = $this->getController();

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber();
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        static::assertNotNull($controller->Response()->headers->get(CookieRemoveHandler::COOKIE_GROUP_COLLECTION_KEY));
    }

    public function testPostDispatchDoesNotAddCookieCollectionHeaderHttpCacheDisabled(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_TECHNICAL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $controller = $this->getController();

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber(false);
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        static::assertNull($controller->Response()->headers->get(CookieRemoveHandler::COOKIE_GROUP_COLLECTION_KEY));
    }

    public function testPostDispatchAddsCookieGroupsViewVariableTechnicallyRequired(): void
    {
        Shopware()->Config()->offsetSet('cookie_note_mode', CookieRemoveSubscriber::COOKIE_MODE_TECHNICAL);
        Shopware()->Config()->offsetSet('show_cookie_note', 1);

        $controller = $this->getController();

        $cookieRemoveSubscriber = $this->getCookieRemoveSubscriber(false);
        $cookieRemoveSubscriber->onPostDispatch($this->getEventArgs($controller));

        static::assertNotNull($controller->View()->getAssign('cookieGroups'));
    }

    private function getEventArgs(\Enlight_Controller_Action $controller = null): \Enlight_Controller_ActionEventArgs
    {
        return new \Enlight_Controller_ActionEventArgs([
            'subject' => $controller ?: $this->getController(),
        ]);
    }

    private function getController(): \Enlight_Controller_Action
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = \Enlight_Class::Instance(\Shopware_Controllers_Frontend_Index::class);
        $controller->setRequest(new \Enlight_Controller_Request_RequestTestCase());
        $controller->setResponse(new \Enlight_Controller_Response_ResponseTestCase());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        return $controller;
    }

    private function getCookieRemoveSubscriber(bool $httpCacheEnabled = true): CookieRemoveSubscriber
    {
        return new CookieRemoveSubscriber(
            Shopware()->Config(),
            Shopware()->Container()->get(CookieHandler::class),
            $httpCacheEnabled
        );
    }
}

class PreserveCookieFromRemovingSubscriber
{
    public function addCookie(): CookieCollection
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct(
            'keepMe',
            '/^keepMe$/',
            'keepMe',
            CookieGroupStruct::PERSONALIZATION
        ));

        return $cookieCollection;
    }
}
