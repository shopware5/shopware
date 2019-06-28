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
 * Interface of Shopware Router 2
 *
 * Replace the default router of shopware 4 / enlight
 *
 * @see \Symfony\Component\Routing\UrlMatcherInterface
 * @see \Symfony\Component\Routing\UrlGeneratorInterface
 * @see http://api.symfony.com/2.0/Symfony/Component/Routing/RouterInterface.html
 * @see \Enlight_Controller_Router
 * @see http://framework.zend.com/manual/1.12/de/zend.controller.router.html#zend.controller.router.subclassing
 */
interface RouterInterface
{
    /**
     * @param array[]|string[] $list
     *
     * @return string[]|false[]
     */
    public function generateList(array $list, Context $context = null);

    /**
     * @param array|string $userParams
     *
     * @return string|false
     */
    public function assemble($userParams = [], Context $context = null);

    /**
     * Switch the context
     */
    public function setContext(Context $context);

    /**
     * @return Context
     */
    public function getContext();

    /**
     * @param string $pathInfo
     *
     * @return array|false
     */
    public function match($pathInfo, Context $context = null);
}
