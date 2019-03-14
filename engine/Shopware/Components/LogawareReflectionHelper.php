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

namespace Shopware\Components;

use Psr\Log\LoggerInterface;

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
     * @return array
     */
    public function unserialize($serialized, $errorSource)
    {
        $classes = [];

        foreach ($serialized as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];

            try {
                $classes[] = $this->reflector->createInstanceFromNamedArguments($className, $arguments);
            } catch (\Exception $e) {
                $this->logger->critical($errorSource . ': ' . $e->getMessage());
            }
        }

        return $classes;
    }
}
