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

namespace Shopware\Bundle\ContentTypeBundle\FieldResolver;

use Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TypeResolver extends AbstractResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function add($item, Field $field): void
    {
        $type = explode('-', $field->getTypeName())[0];
        if (!isset($this->storage[$type])) {
            $this->storage[$type] = [];
            $this->resolveIds[$type] = [];
        }

        $values = array_filter(explode('|', $item));

        foreach ($values as $value) {
            if (isset($this->storage[$type][$value]) || isset($this->resolveIds[$type][$value])) {
                return;
            }

            $this->resolveIds[$type][] = $value;
        }
    }

    public function resolve(): void
    {
        foreach ($this->resolveIds as $type => $values) {
            if (empty($values)) {
                continue;
            }
            $this->resolveIds[$type] = [];

            $service = $this->getTypeService($type);

            $criteria = new Criteria();
            $criteria->filter = [['property' => 'id', 'value' => $values]];

            foreach ($service->findAll($criteria)->items as $item) {
                $this->storage[$type][$item['id']] = $item;
            }
        }
    }

    public function get($item, Field $field)
    {
        $type = explode('-', $field->getTypeName())[0];
        $values = array_filter(explode('|', $item));

        if (!$field->getType()::isMultiple()) {
            if (isset($this->storage[$type][$values[0]])) {
                return $this->storage[$type][$values[0]];
            }

            return null;
        }

        $result = [];

        foreach ($values as $value) {
            if (isset($this->storage[$type][$value])) {
                $result[] = $this->storage[$type][$value];
            }
        }

        return $result;
    }

    public function getTypeService(string $type): RepositoryInterface
    {
        return $this->container->get('shopware.bundle.content_type.' . $type);
    }
}
