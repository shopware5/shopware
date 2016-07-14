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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VoteAverageFacetHandler implements FacetHandlerInterface
{
    /**
     * @var QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        Connection $connection,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->connection = $connection;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('rating')) {
            $this->fieldName = 'rating';
        }
    }

    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param FacetInterface|VoteAverageFacet $facet
     * @param Criteria $criteria
     * @param Struct\ShopContextInterface $context
     * @return \Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult|null
     */
    public function generateFacet(
        FacetInterface $facet,
        Criteria $criteria,
        Struct\ShopContextInterface $context
    ) {
        $queryCriteria = clone $criteria;
        $queryCriteria->resetConditions();
        $queryCriteria->resetSorting();

        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);

        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        if (!$query->hasState(VoteAverageCondition::STATE_INCLUDES_VOTE_TABLE)) {
            $query->innerJoin(
                'product',
                's_articles_vote',
                'vote',
                'vote.articleID = product.id'
            );
        }

        $query->select('COUNT(vote.id) as hasVotes');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $data = $statement->fetch(\PDO::FETCH_COLUMN);

        if (!$data) {
            return null;
        }

        $activeAverage = null;
        if ($criteria->hasCondition($facet->getName())) {
            /**@var $condition VoteAverageCondition*/
            $condition = $criteria->getCondition($facet->getName());
            $activeAverage = $condition->getAverage();
        }

        $values = [
            new ValueListItem(1, '', ($activeAverage == 1)),
            new ValueListItem(2, '', ($activeAverage == 2)),
            new ValueListItem(3, '', ($activeAverage == 3)),
            new ValueListItem(4, '', ($activeAverage == 4)),
            new ValueListItem(5, '', ($activeAverage == 5)),
        ];

        return new RadioFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $this->snippetNamespace->get($facet->getName(), 'Ranking'),
            $values,
            $this->fieldName,
            [],
            'frontend/listing/filter/facet-rating.tpl'
        );
    }

    /**
     * Checks if the passed facet can be handled by this class.
     * @param FacetInterface $facet
     * @return bool
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof VoteAverageFacet);
    }
}
