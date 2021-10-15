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

namespace Shopware\Bundle\AttributeBundle\Repository;

use Shopware\Bundle\AttributeBundle\Repository\Reader\ReaderInterface;
use Shopware\Bundle\AttributeBundle\Repository\Searcher\SearcherInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Bundle\EsBackendBundle\EsAwareRepository;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Bundle\ESIndexingBundle\TextMappingInterface;
use Shopware\Components\Model\ModelManager;

class OrderRepository extends GenericRepository implements EsAwareRepository
{
    /**
     * @var TextMappingInterface
     */
    private $textMapping;

    /**
     * @param string $entity
     */
    public function __construct(
        $entity,
        ModelManager $entityManager,
        ReaderInterface $reader,
        SearcherInterface $searcher,
        TextMappingInterface $textMapping
    ) {
        parent::__construct($entity, $entityManager, $reader, $searcher);
        $this->textMapping = $textMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select(['orders.id', 'orders.id']);
        $query->from('s_order', 'orders');
        $query->andWhere('orders.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->addOrderBy('orders.id');
        $query->setMaxResults(50);

        return new LastIdQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getMapping()
    {
        return [
            'properties' => [
                'id' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'number' => $this->getTextFieldWithRawData(),
                'invoiceAmount' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
                'invoiceShipping' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,
                'orderTime' => TypeMappingInterface::MAPPING_DATE_AND_DATE_TIME_FIELD,
                'status' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'cleared' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'customerId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'supplierId' => $this->textMapping->getTextField(),
                'billingCountryId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'shippingCountryId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'groupKey' => $this->textMapping->getKeywordField(),
                'email' => $this->getTextFieldWithRawData(),
                'orderDocuments' => $this->getTextFieldWithRawData(),
                'transactionId' => $this->textMapping->getKeywordField(),
                'firstname' => $this->getTextFieldWithRawData(),
                'lastname' => $this->getTextFieldWithRawData(),
                'paymentId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'paymentName' => $this->textMapping->getTextField(),
                'dispatchId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'dispatchName' => $this->textMapping->getTextField(),
                'shopId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'shopName' => $this->textMapping->getTextField(),
                'title' => $this->textMapping->getTextField(),
                'company' => $this->textMapping->getTextField(),
                'department' => $this->textMapping->getTextField(),
                'street' => $this->textMapping->getTextField(),
                'zipcode' => $this->textMapping->getKeywordField(),
                'city' => $this->textMapping->getTextField(),
                'phone' => $this->textMapping->getKeywordField(),
                'countryName' => $this->textMapping->getTextField(),

                'swag_all' => $this->textMapping->getTextField(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDomainName()
    {
        return 'orders';
    }

    private function getTextFieldWithRawData()
    {
        return array_merge(
            $this->textMapping->getTextField(),
            [
                'fields' => [
                    'raw' => $this->textMapping->getKeywordField(),
                ],
                'copy_to' => 'swag_all',
            ]
        );
    }
}
