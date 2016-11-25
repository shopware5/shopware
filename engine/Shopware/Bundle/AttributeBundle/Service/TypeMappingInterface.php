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
namespace Shopware\Bundle\AttributeBundle\Service;

use Doctrine\DBAL\Types\Type;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
interface TypeMappingInterface
{
    /**
     * @return array
     */
    public function getTypes();

    /**
     * @return string[]
     */
    public function getEntities();

    /**
     * @param Type $type
     *
     * @return string
     */
    public function dbalToUnified(Type $type);

    /**
     * @param string $type
     *
     * @return string
     */
    public function unifiedToSQL($type);

    /**
     * @param string $unified
     *
     * @return array
     */
    public function unifiedToElasticSearch($unified);
}
