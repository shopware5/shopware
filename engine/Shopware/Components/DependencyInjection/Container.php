<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\DependencyInjection;

use Shopware\Components\ResourceLoader;
use Symfony\Component\DependencyInjection\Container as BaseContainer;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Container extends BaseContainer
{
    /**
     * @var ResourceLoader
     */
    protected $resourceLoader;

    /**
     * @param ResourceLoader $resourceLoader
     */
    public function setResourceLoader(ResourceLoader $resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * Wraps container get call to resource loader.
     * So the resource loader is able to trigger events
     * for internal loaded service dependencies
     *
     * {@inheritdoc}
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (null === $this->resourceLoader) {
            return parent::get($id, $invalidBehavior);
        }

        return $this->resourceLoader->get($id);
    }

    /**
     * Returns service directly from container
     * {@inheritdoc}
     */
    public function getService($id)
    {
        return parent::get($id);
    }

    /**
     * @param $id
     * @return string
     */
    public function getNormalizedId($id)
    {
        $id = strtolower($id);

        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return $id;
    }
}
