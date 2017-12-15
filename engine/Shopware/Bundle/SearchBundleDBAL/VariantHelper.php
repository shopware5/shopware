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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomFacetGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomListingHydrator;
use Shopware\Components\ReflectionHelper;

class VariantHelper implements VariantHelperInterface
{
    const TABLE_JOINED = 'all_variants';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CustomFacetGatewayInterface
     */
    protected $customFacetGateway;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var ReflectionHelper
     */
    private $reflectionHelper;

    /**
     * VariantHelper constructor.
     *
     * @param Connection            $connection
     * @param CustomListingHydrator $customFacetGateway
     * @param FieldHelper           $fieldHelper
     */
    public function __construct(
        Connection $connection,
        CustomListingHydrator $customFacetGateway,
        FieldHelper $fieldHelper)
    {
        $this->connection = $connection;
        $this->customFacetGateway = $customFacetGateway;
        $this->fieldHelper = $fieldHelper;
        $this->reflectionHelper = new ReflectionHelper();
    }

    public function joinVariants(QueryBuilder $query)
    {
        if ($query->hasState(self::TABLE_JOINED)) {
            return;
        }

        $query->innerJoin(
            'product',
            's_articles_details',
            'allVariants',
            'allVariants.articleID = product.id AND allVariants.active = 1'
        );

        $query->addState(self::TABLE_JOINED);
    }

    /**
     * Checks if the option id belongs to the groups which should expand
     *
     * @param array        $optionIds
     * @param VariantFacet $variantFacet
     *
     * @return bool
     */
    public function shouldExpandGroup(array $optionIds, VariantFacet $variantFacet = null)
    {
        if (!$variantFacet) {
            $variantFacet = $this->getVariantFacet();
        }

        if (empty($variantFacet)) {
            return false;
        }

        $expandGroupsIds = $variantFacet->getExpandGroupIds();

        if (empty($expandGroupsIds)) {
            return false;
        }

        $query = $this->connection->createQueryBuilder();
        $query->addSelect('count(id) count');
        $query->from('s_article_configurator_options');
        $query->andWhere('id IN (:ids)');
        $query->andWhere('group_id IN (:groups)');
        $query->setParameter('ids', $optionIds, Connection::PARAM_INT_ARRAY);
        $query->setParameter('groups', $expandGroupsIds, Connection::PARAM_INT_ARRAY);

        $shouldExpand = (int) $query->execute()->fetch(\PDO::FETCH_COLUMN);

        if ($shouldExpand === 0) {
            return false;
        }

        return true;
    }

    /**
     * Return the group id of the option id.
     *
     * @param int $optionId
     *
     * @return int
     */
    public function getGroupIdByOptionId($optionId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect('DISTINCT group_id');
        $query->from('s_article_configurator_options');
        $query->andWhere('id IN (:ids)');
        $query->setParameter('ids', $optionId);

        return (int) $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns the VariantFacet.
     *
     * @return null|object
     */
    public function getVariantFacet()
    {
        $json = $this->connection->createQueryBuilder()
            ->addSelect('facet')
            ->from('s_search_custom_facet')
            ->where('unique_key = :key')
            ->andWhere('active = 1')
            ->setParameter('key', 'VariantFacet')
            ->execute()
            ->fetchColumn(0);

        if (empty($json)) {
            return null;
        }

        $arr = json_decode($json, true);

        if (!empty($arr)) {
            return $this->reflectionHelper->createInstanceFromNamedArguments(key($arr), reset($arr));
        }

        return null;
    }
}
