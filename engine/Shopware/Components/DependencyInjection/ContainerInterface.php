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
namespace Shopware\Components\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface as BaseContainerInterface;

/**
 * Interface ContainerInterface
 * @package Shopware\Components\DependencyInjection
 */
interface ContainerInterface extends BaseContainerInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function initialized($name);

    /**
     * @param string $id
     * @return string
     */
    public function getNormalizedId($id);

    /**
     * Loads the given resource.
     *
     * @param string $name
     *
     * @throws \Exception
     * @throws \Enlight_Exception
     *
     * @return bool
     */
    public function load($name);

    /**
     * If the given resource is set, the resource and the resource status are removed from the
     * list properties.
     *
     * @param string $name
     *
     * @return Container
     */
    public function reset($name = null);
}