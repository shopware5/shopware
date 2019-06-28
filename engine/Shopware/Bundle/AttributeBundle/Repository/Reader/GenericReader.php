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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;

class GenericReader implements ReaderInterface
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @param string $entity
     */
    public function __construct($entity, ModelManager $entityManager)
    {
        $this->entity = $entity;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int[]|string[] $identifiers
     *
     * @return array[]
     */
    public function getList($identifiers)
    {
        $query = $this->createListQuery();
        $query->andWhere($this->getIdentifierField() . ' IN (:ids)');
        $query->setParameter('ids', $identifiers, Connection::PARAM_STR_ARRAY);
        $data = $query->getQuery()->getArrayResult();
        $result = [];

        $identifiers = array_map('strtolower', $identifiers);
        $data = array_change_key_case($data, CASE_LOWER);
        $identifierFields = explode('.', $this->getIdentifierField());
        $identifierField = array_pop($identifierFields);

        foreach ($identifiers as $id) {
            if (!isset($data[$id])) {
                continue;
            }

            $originalId = $id;
            $row = $data[$id];

            if (array_key_exists($identifierField, $row)) {
                $originalId = $row[$identifierField];
            }

            $result[$originalId] = $row;
        }

        return $result;
    }

    /**
     * @param int|string $identifier
     *
     * @return array
     */
    public function get($identifier)
    {
        $query = $this->createDetailQuery();
        $query->andWhere($this->getIdentifierField() . ' = :id');
        $query->setParameter('id', $identifier);
        $data = $query->getQuery()->getArrayResult();

        return array_shift($data);
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function createDetailQuery()
    {
        return $this->createListQuery();
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function createListQuery()
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select('entity');
        $query->from($this->entity, 'entity', $this->getIdentifierField());

        return $query;
    }

    /**
     * @return string
     */
    protected function getIdentifierField()
    {
        return 'entity.id';
    }
}
