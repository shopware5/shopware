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

namespace Shopware\Bundle\ESIndexingBundle\Struct;

/**
 * Class IndexConfiguration
 */
class IndexConfiguration
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var int|null
     */
    private $numberOfShards = null;

    /**
     * @var int|null
     */
    private $numberOfReplicas = null;

    /**
     * @param string   $name
     * @param string   $alias
     * @param int|null $numberOfShards
     * @param int|null $numberOfReplicas
     */
    public function __construct($name, $alias, $numberOfShards = null, $numberOfReplicas = null)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->numberOfShards = $numberOfShards;
        $this->numberOfReplicas = $numberOfReplicas;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return int|null
     */
    public function getNumberOfShards()
    {
        return $this->numberOfShards;
    }

    /**
     * @param int|null $numberOfShards
     */
    public function setNumberOfShards($numberOfShards)
    {
        $this->numberOfShards = $numberOfShards;
    }

    /**
     * @return int|null
     */
    public function getNumberOfReplicas()
    {
        return $this->numberOfReplicas;
    }

    /**
     * @param int|null $numberOfReplicas
     */
    public function setNumberOfReplicas($numberOfReplicas)
    {
        $this->numberOfReplicas = $numberOfReplicas;
    }
}
