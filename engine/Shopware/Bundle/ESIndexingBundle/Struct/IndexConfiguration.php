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
     * @var array
     */
    private $config;

    /**
     * @param string   $name
     * @param string   $alias
     * @param int|null $numberOfShards
     * @param int|null $numberOfReplicas
     * @param int|null $totalFieldsLimit
     * @param int|null $maxResultWindow
     */
    public function __construct(
        $name,
        $alias,
        $numberOfShards = null,
        $numberOfReplicas = null,
        $totalFieldsLimit = null,
        $maxResultWindow = null,
        ?array $indexConfiguration = []
    ) {
        $this->name = $name;
        $this->alias = $alias;
        $this->config = $indexConfiguration;

        if ($numberOfShards) {
            $this->config['numberOfShards'] = $numberOfShards;
        }

        if ($numberOfReplicas) {
            $this->config['numberOfReplicas'] = $numberOfReplicas;
        }

        if ($totalFieldsLimit) {
            $this->config['totalFieldsLimit'] = $totalFieldsLimit;
        }

        if ($maxResultWindow) {
            $this->config['maxResultWindow'] = $maxResultWindow;
        }
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
     * @deprecated Use toArray instead, will be removed with 5.7
     *
     * @return int|null
     */
    public function getNumberOfShards()
    {
        return $this->config['numberOfShards'] ?? null;
    }

    /**
     * @deprecated Use setConfig instead, will be removed with 5.7
     *
     * @param int|null $numberOfShards
     */
    public function setNumberOfShards($numberOfShards)
    {
        $this->config['numberOfShards'] = $numberOfShards;
    }

    /**
     * @deprecated Use toArray instead, will be removed with 5.7
     *
     * @return int|null
     */
    public function getNumberOfReplicas()
    {
        return $this->config['numberOfReplicas'] ?? null;
    }

    /**
     * @deprecated Use setConfig instead, will be removed with 5.7
     *
     * @param int|null $numberOfReplicas
     */
    public function setNumberOfReplicas($numberOfReplicas)
    {
        $this->config['numberOfReplicas'] = $numberOfReplicas;
    }

    /**
     * @deprecated Use toArray instead, will be removed with 5.7
     *
     * @return int|null
     */
    public function getTotalFieldsLimit()
    {
        return $this->config['totalFieldsLimit'] ?? null;
    }

    /**
     * @deprecated Use setConfig instead, will be removed with 5.7
     *
     * @param int|null $totalFieldsLimit
     */
    public function setTotalFieldsLimit($totalFieldsLimit)
    {
        $this->config['totalFieldsLimit'] = $totalFieldsLimit;
    }

    /**
     * @deprecated Use toArray instead, will be removed with 5.7
     *
     * @return int|null
     */
    public function getMaxResultWindow()
    {
        return $this->config['maxResultWindow'] ?? null;
    }

    /**
     * @deprecated Use setConfig instead, will be removed with 5.7
     *
     * @param int|null $maxResultWindow
     */
    public function setMaxResultWindow($maxResultWindow)
    {
        $this->config['maxResultWindow'] = $maxResultWindow;
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
