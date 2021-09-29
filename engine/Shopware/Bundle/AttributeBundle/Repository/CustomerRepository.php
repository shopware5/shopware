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

class CustomerRepository extends GenericRepository implements EsAwareRepository
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
        $query->select(['customer.id', 'customer.id']);
        $query->from('s_user', 'customer');
        $query->andWhere('customer.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->addOrderBy('customer.id');
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
                'email' => $this->getTextFieldWithRawData(),
                'active' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'title' => $this->getTextFieldWithRawData(),
                'salutation' => $this->textMapping->getKeywordField(),
                'firstname' => $this->getTextFieldWithRawData(),
                'lastname' => $this->getTextFieldWithRawData(),
                'lastLogin' => TypeMappingInterface::MAPPING_DATE_TIME_FIELD,
                'firstLogin' => TypeMappingInterface::MAPPING_DATE_TIME_FIELD,
                'newsletter' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'birthday' => TypeMappingInterface::MAPPING_DATE_FIELD,
                'lockedUntil' => TypeMappingInterface::MAPPING_DATE_TIME_FIELD,
                'accountMode' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'shopId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'shopName' => $this->getTextFieldWithRawData(),
                'company' => $this->getTextFieldWithRawData(),
                'department' => $this->getTextFieldWithRawData(),
                'street' => $this->getTextFieldWithRawData(),
                'zipcode' => $this->getTextFieldWithRawData(),
                'city' => $this->getTextFieldWithRawData(),
                'phone' => $this->getTextFieldWithRawData(),
                'countryId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'countryName' => $this->textMapping->getKeywordField(),
                'customerGroupId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'customerGroupName' => $this->textMapping->getKeywordField(),

                'swag_all' => $this->textMapping->getTextField(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDomainName()
    {
        return 'customer';
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
