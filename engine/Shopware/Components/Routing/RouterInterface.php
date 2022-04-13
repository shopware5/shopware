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

namespace Shopware\Components\Routing;

/**
 * @see \Symfony\Component\Routing\Matcher\UrlMatcherInterface
 * @see \Symfony\Component\Routing\Generator\UrlGeneratorInterface
 * @see https://symfony.com/doc/4.4/routing.html
 * @see \Enlight_Controller_Router
 * @see http://framework.zend.com/manual/1.12/de/zend.controller.router.html#zend.controller.router.subclassing
 */
interface RouterInterface
{
    /**
     * @param array<int, array<string, mixed>>|array<int, string> $list
     *
     * @return array<int, string>
     */
    public function generateList(array $list, Context $context = null);

    /**
     * @param array<string, mixed> $userParams
     *
     * @return string
     */
    public function assemble($userParams = [], Context $context = null);

    /**
     * Switch the context
     *
     * @return void
     */
    public function setContext(Context $context);

    /**
     * @return Context
     */
    public function getContext();

    /**
     * @param string $pathInfo
     *
     * @return array<string, mixed>|false
     */
    public function match($pathInfo, Context $context = null);
}
