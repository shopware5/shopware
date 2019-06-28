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

use Shopware\Bundle\ContentTypeBundle\Field\ResolveableFieldInterface;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\AbstractResolver;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TypeFieldResolver implements TypeFieldResolverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AbstractResolver[]
     */
    protected $resolver = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolveFields(Type $type, array $items): array
    {
        foreach ($items as $item) {
            foreach ($type->getFields() as $field) {
                if (!$field->getType() instanceof ResolveableFieldInterface) {
                    continue;
                }

                $key = $field->getName();

                if (empty($item[$key])) {
                    continue;
                }

                $this->getResolver($field->getType()::getResolver())->add($item[$key], $field);
            }
        }

        foreach ($this->resolver as $resolver) {
            $resolver->resolve();
        }

        foreach ($items as &$item) {
            foreach ($type->getFields() as $field) {
                if (!$field->getType() instanceof ResolveableFieldInterface) {
                    continue;
                }

                $key = $field->getName();

                if (empty($item[$key])) {
                    continue;
                }

                $item[$key] = $this->getResolver($field->getType()::getResolver())->get($item[$key], $field);
            }
        }

        return $items;
    }

    protected function getResolver(string $name): AbstractResolver
    {
        if (!isset($this->resolver[$name])) {
            /** @var AbstractResolver $resolver */
            $resolver = $this->container->get($name);
            $this->resolver[$name] = $resolver;
        }

        return $this->resolver[$name];
    }
}
