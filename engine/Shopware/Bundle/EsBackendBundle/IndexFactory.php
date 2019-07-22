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

namespace Shopware\Bundle\EsBackendBundle;

use Shopware\Bundle\ESIndexingBundle\Struct\IndexConfiguration;

class IndexFactory implements IndexFactoryInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $indexConfig;

    public function __construct(string $prefix, array $indexConfig)
    {
        $this->prefix = $prefix;
        $this->indexConfig = $indexConfig;
    }

    public function createIndexConfiguration(string $name): IndexConfiguration
    {
        return new IndexConfiguration(
            $this->getIndexName($name),
            $this->getAlias($name),
            null,
            null,
            null,
            null,
            $this->indexConfig
        );
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    private function getIndexName(string $name): string
    {
        return $this->getAlias($name) . '_' . (new \DateTime())->format('YmdHis');
    }

    private function getAlias(string $name): string
    {
        return $this->prefix . $name;
    }
}
