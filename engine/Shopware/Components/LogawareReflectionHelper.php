<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\SortingInterface;

class LogawareReflectionHelper
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReflectionHelper
     */
    private $reflector;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->reflector = new ReflectionHelper();
    }

    /**
     * @param array  $serialized
     * @param string $errorSource
     *
     * @return array<ConditionInterface|SortingInterface|FacetInterface>
     */
    public function unserialize($serialized, $errorSource)
    {
        $classes = [];

        foreach ($serialized as $className => $arguments) {
            $className = explode('|', $className);
            /** @var class-string<ConditionInterface|SortingInterface|FacetInterface> $className */
            $className = $className[0];

            try {
                $classes[] = $this->reflector->createInstanceFromNamedArguments($className, $arguments);
            } catch (Exception $e) {
                $this->logger->critical($errorSource . ': ' . $e->getMessage());
            }
        }

        return $classes;
    }
}
