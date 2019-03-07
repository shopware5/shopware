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
use Shopware\Bundle\EsBackendBundle\EsAwareRepository;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Bundle\ESIndexingBundle\TextMappingInterface;
use Shopware\Components\Model\ModelManager;

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
        return in_array($entity, [
            \Shopware\Models\Article\Article::class,
            \Shopware\Models\Article\Detail::class,
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
                'id' => ['type' => 'long'],
                'number' => array_merge($this->textMapping->getKeywordField(), ['copy_to' => 'swag_all']),
                'categoryIds' => ['type' => 'long'],
                'variantId' => ['type' => 'long'],
                'taxId' => ['type' => 'long'],
                'articleId' => ['type' => 'long'],
                'kind' => ['type' => 'long'],
                'name' => array_merge(
                    $this->textMapping->getTextField(),
                    [
                        'fields' => [
                            'raw' => $this->textMapping->getKeywordField(),
                        ],
                        'copy_to' => 'swag_all',
                    ]
                ),
                'inStock' => ['type' => 'long'],
                'ean' => $this->textMapping->getKeywordField(),
                'supplierNumber' => $this->textMapping->getKeywordField(),
                'additionalText' => $this->textMapping->getTextField(),
                'articleActive' => ['type' => 'boolean'],
                'variantActive' => ['type' => 'boolean'],
                'supplierId' => ['type' => 'long'],
                'supplierName' => array_merge(
                    $this->textMapping->getTextField(),
                    [
                        'fields' => [
                            'raw' => $this->textMapping->getKeywordField(),
                        ],
                        'copy_to' => 'swag_all',
                    ]
                ),
                'price' => ['type' => 'double'],

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
