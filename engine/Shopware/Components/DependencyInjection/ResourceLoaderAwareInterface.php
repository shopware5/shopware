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

/**
 * ResourceLoaderAwareInterface should be implemented by classes that depends on a ResourceLoader.
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
interface ResourceLoaderAwareInterface
{
    /**
     * Sets the ResourceLoader.
     *
     * @param ResourceLoader|null $ResourceLoader A ResourceLoaderInterface instance or null
     */
    public function setResourceLoader(ResourceLoader $ResourceLoader = null);
}
