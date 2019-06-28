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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class TypeProvider
{
    private $types = [];

    public function __construct(array $types, TypeBuilder $typeBuilder)
    {
        foreach ($types as $name => $type) {
            $this->types[$name] = $typeBuilder->createType($name, $type);
        }
    }

    /**
     * @return array<string, Type>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getType(string $name): Type
    {
        if (!isset($this->types[$name])) {
            throw new \RuntimeException(sprintf('Requested type "%s" does not exist', $name));
        }

        return $this->types[$name];
    }

    public function addType(string $name, Type $type): void
    {
        $this->types[$name] = $type;
    }

    public function removeType(string $name): void
    {
        unset($this->types[$name]);
    }

    public function getTypeByControllerName(string $controllerName): Type
    {
        $controllerName = strtolower($controllerName);

        foreach ($this->types as $type) {
            if (strtolower($type->getControllerName()) === $controllerName) {
                return $type;
            }
        }

        throw new \RuntimeException(sprintf('Cannot find type for controller "%s"', $controllerName));
    }
}
