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

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * API Manger
 */
class Manager
{
    /**
     * @param string $name
     *
     * @return Resource\Resource
     *
     * @deprecated with 5.6, will be removed with 5.8. Inject the resource instead
     */
    public static function getResource($name)
    {
        trigger_error('Using Manager::getResource is deprecated since 5.6 and will be removed with 5.8. Inject the resource instead', E_USER_DEPRECATED);

        $container = Shopware()->Container();
        try {
            /** @var Resource\Resource $resource */
            $serviceId = 'shopware.api.' . (new CamelCaseToSnakeCaseNameConverter())->normalize($name);
            if ($container->has($serviceId)) {
                $resource = $container->get($serviceId);
            } else {
                trigger_error(sprintf('The requested service with id %s is deprecated. Please use CamelCased service id instead.', $name), E_USER_DEPRECATED);
                $resource = $container->get('shopware.api.' . strtolower($name));
            }
        } catch (ServiceNotFoundException $e) {
            $name = ucfirst($name);
            $class = __NAMESPACE__ . '\\Resource\\' . $name;

            /** @var Resource\Resource $resource */
            $resource = new $class();

            $resource->setContainer($container);
            $resource->setManager($container->get('models'));
        }

        if ($container->initialized('auth')) {
            $resource->setAcl($container->get('acl'));
            $resource->setRole($container->get('auth')->getIdentity()->role);
        }

        return $resource;
    }
}
