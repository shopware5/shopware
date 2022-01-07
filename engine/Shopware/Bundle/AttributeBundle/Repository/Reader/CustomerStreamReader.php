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
    private CustomerStreamRepositoryInterface $repository;

    public function __construct(string $entity, ModelManager $entityManager, CustomerStreamRepositoryInterface $repository)
    {
        parent::__construct($entity, $entityManager);
        $this->repository = $repository;
    }

    public function getList($identifiers)
    {
        $customerStreams = parent::getList($identifiers);

        $identifiers = array_map('\intval', $identifiers);
        $customerAndNewsletterCountByStream = $this->repository->fetchStreamsCustomerCount($identifiers);

        foreach ($customerStreams as &$customerStream) {
            $id = (int) $customerStream['id'];
            if (\array_key_exists($id, $customerAndNewsletterCountByStream)) {
                $customerStream = array_merge($customerStream, $customerAndNewsletterCountByStream[$id]);
            } else {
                $customerStream['customer_count'] = 0;
                $customerStream['newsletter_count'] = 0;
            }
        }

        return $customerStreams;
    }
}
