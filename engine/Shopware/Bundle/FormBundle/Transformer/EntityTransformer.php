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

namespace Shopware\Bundle\FormBundle\Transformer;

use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<ModelEntity, int>
 */
class EntityTransformer implements DataTransformerInterface
{
    private ModelManager $modelManager;

    /**
     * @var class-string<ModelEntity>
     */
    private string $entityName;

    /**
     * @param class-string<ModelEntity> $entityName
     */
    public function __construct(ModelManager $modelManager, string $entityName)
    {
        $this->modelManager = $modelManager;
        $this->entityName = $entityName;
    }

    public function transform($entity)
    {
        if ($entity === null) {
            return null;
        }

        if (!method_exists($entity, 'getId')) {
            return null;
        }

        return $entity->getId();
    }

    public function reverseTransform($entityId)
    {
        if (!$entityId) {
            return null;
        }

        $entity = $this->modelManager->find($this->entityName, $entityId);

        if ($entity === null) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf('An entity with id "%s" does not exist! (%s)', $entityId, $this->entityName));
        }

        return $entity;
    }
}
