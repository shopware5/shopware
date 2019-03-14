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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;

class CustomerStreamReader extends GenericReader
{
    /**
     * @var \Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface
     */
    private $repository;

    /**
     * @param string $entity
     */
    public function __construct($entity, ModelManager $entityManager, CustomerStreamRepositoryInterface $repository)
    {
        parent::__construct($entity, $entityManager);
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($identifiers)
    {
        $data = parent::getList($identifiers);

        $counts = $this->repository->fetchStreamsCustomerCount($identifiers);

        foreach ($data as &$row) {
            $id = (int) $row['id'];
            if (!array_key_exists($id, $counts)) {
                $row['customer_count'] = 0;
                $row['newsletter_count'] = 0;
            } else {
                $row = array_merge($row, $counts[$id]);
            }
        }

        return $data;
    }
}
