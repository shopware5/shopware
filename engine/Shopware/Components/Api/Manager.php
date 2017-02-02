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

namespace Shopware\Components\Api;

use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * API Manger
 *
 * @category  Shopware
 * @package   Shopware\Components\Api
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * @param $name
     * @return Resource\Resource
     */
    public static function getResource($name)
    {
        $container = Shopware()->Container();
        try {
            /** @var $resource Resource\Resource */
            $resource = $container->get('shopware.api.' . strtolower($name));
        } catch (ServiceNotFoundException $e) {
            $name = ucfirst($name);
            $class = __NAMESPACE__ . '\\Resource\\' . $name;

            /** @var $resource Resource\Resource */
            $resource = new $class();
        }

        if ($resource instanceof ContainerAwareInterface) {
            $resource->setContainer($container);
        }

        $resource->setManager($container->get('models'));

        if ($container->initialized('Auth')) {
            $resource->setAcl($container->get('acl'));
            $resource->setRole($container->get('auth')->getIdentity()->role);
        }

        return $resource;
    }
}
