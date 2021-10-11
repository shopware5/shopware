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
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;

class ProductRepository extends GenericRepository implements EsAwareRepository
{
    /**
     * @var TextMappingInterface
     */
    protected $textMapping;

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
    public function supports($entity)
    {
        return \in_array($entity, [
            Article::class,
            Detail::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();

        $query = $query
            ->select(['products.id', 'products.ordernumber'])
            ->from('s_articles_details', 'products')
            ->andWhere('products.id > :lastId')
            ->setParameter(':lastId', 0)
            ->addOrderBy('products.id')
            ->setMaxResults(50);

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
                'number' => array_merge($this->textMapping->getKeywordField(), ['copy_to' => 'swag_all']),
                'categoryIds' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'variantId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'taxId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'articleId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'kind' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'name' => array_merge(
                    $this->textMapping->getTextField(),
                    [
                        'fields' => [
                            'raw' => $this->textMapping->getKeywordField(),
                        ],
                        'copy_to' => 'swag_all',
                    ]
                ),
                'inStock' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'ean' => $this->textMapping->getKeywordField(),
                'supplierNumber' => $this->textMapping->getKeywordField(),
                'additionalText' => $this->textMapping->getTextField(),
                'articleActive' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'variantActive' => TypeMappingInterface::MAPPING_BOOLEAN_FIELD,
                'supplierId' => TypeMappingInterface::MAPPING_LONG_FIELD,
                'supplierName' => array_merge(
                    $this->textMapping->getTextField(),
                    [
                        'fields' => [
                            'raw' => $this->textMapping->getKeywordField(),
                        ],
                        'copy_to' => 'swag_all',
                    ]
                ),
                'price' => TypeMappingInterface::MAPPING_DOUBLE_FIELD,

                'swag_all' => $this->textMapping->getTextField(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDomainName()
    {
        return 'product';
    }
}
