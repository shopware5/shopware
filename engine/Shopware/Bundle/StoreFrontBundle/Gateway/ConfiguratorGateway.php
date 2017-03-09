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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfiguratorGateway
{
    /**
     * @var Hydrator\ConfiguratorHydrator
     */
    private $configuratorHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGateway
     */
    private $mediaGateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection                                                      $connection
     * @param FieldHelper                                                     $fieldHelper
     * @param Hydrator\ConfiguratorHydrator                                   $configuratorHydrator
     * @param \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGateway $mediaGateway
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\ConfiguratorHydrator $configuratorHydrator,
        Gateway\MediaGateway $mediaGateway
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->mediaGateway = $mediaGateway;
    }

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set requires the following data:
     * - Configurator set
     * - Core attribute of the configurator set
     * - Groups of the configurator set
     * - Options of each group
     *
     * Required translation in the provided context language:
     * - Configurator groups
     * - Configurator options
     *
     * The configurator groups and options has to be sorted in the following order:
     * - Group position
     * - Group name
     * - Option position
     * - Option name
     *
     * @param Struct\BaseProduct          $product
     * @param Struct\ShopContextInterface $context
     *
     * @return Struct\Configurator\Set
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $query = $this->getQuery();
        $query->addSelect($this->fieldHelper->getConfiguratorSetFields())
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);

        $query->where('products.id = :id')
            ->setParameter(':id', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->configuratorHydrator->hydrate($data);
    }


    /**
     * Selects the first possible product image for the provided product configurator.
     * The product images are selected over the image mapping.
     * The image mapping defines which product image should be displayed for which configurator selection.
     * Returns for each configurator option the first possible image
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct          $product
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
     *
     * @return Struct\Media[] indexed by the configurator option id
     */
    public function getConfiguratorMedia(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $subQuery = $this->connection->createQueryBuilder();

        $subQuery->select('image.media_id')
            ->from('s_articles_img', 'image')
            ->innerJoin('image', 's_article_img_mappings', 'mapping', 'mapping.image_id = image.id')
            ->innerJoin('mapping', 's_article_img_mapping_rules', 'rules', 'rules.mapping_id = mapping.id')
            ->where('image.articleID = product.id')
            ->andWhere('rules.option_id = optionRelation.option_id')
            ->orderBy('image.position')
            ->setMaxResults(1)
        ;

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'optionRelation.option_id',
            '(' . $subQuery->getSQL() . ') as media_id',
        ]);

        $query->from('s_articles', 'product')
            ->innerJoin('product', 's_article_configurator_set_option_relations', 'optionRelation', 'product.configurator_set_id = optionRelation.set_id')
            ->where('product.id = :articleId')
            ->groupBy('optionRelation.option_id')
            ->setParameter(':articleId', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
        $data = array_filter($data);

        $media = $this->mediaGateway->getList($data, $context);

        $result = [];
        foreach ($data as $optionId => $mediaId) {
            if (!isset($media[$mediaId])) {
                continue;
            }
            $result[$optionId] = $media[$mediaId];
        }

        return $result;
    }

    /**
     * Returns all possible configurator combinations for the provided product.
     * The returned array contains as array key the id of the configurator option.
     * The array value contains an imploded array with all possible configurator option ids
     * which can be combined with the option.
     *
     * Example (written with the configurator option names)
     * array(
     *     'white' => array('XL', 'L'),
     *     'red'   => array('S', ...)
     * )
     *
     * If the configurator contains only one group the function has to return an array indexed
     * by the ids, which are selectable:
     *
     * Example (written with the configurator option names)
     * array(
     *     'white' => array(),
     *     'red'   => array()
     * )
     *
     * @param Struct\BaseProduct $product
     *
     * @return array Indexed by the option id
     */
    public function getProductCombinations(Struct\BaseProduct $product)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'relations.option_id',
            "GROUP_CONCAT(DISTINCT assignedRelations.option_id, '' SEPARATOR '|') as combinations",
        ]);

        $query->from('s_article_configurator_option_relations', 'relations')
            ->innerJoin('relations', 's_articles_details', 'variant', 'variant.id = relations.article_id AND variant.articleID = :articleId AND variant.active = 1')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID AND (product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)')
            ->leftJoin('relations', 's_article_configurator_option_relations', 'assignedRelations', 'assignedRelations.article_id = relations.article_id AND assignedRelations.option_id != relations.option_id')
            ->groupBy('relations.option_id')
            ->setParameter(':articleId', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($data as &$row) {
            $row = explode('|', $row);
        }

        return $data;
    }

    /**
     * @return QueryBuilder
     */
    private function getQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_sets', 'configuratorSet')
            ->innerJoin('configuratorSet', 's_articles', 'products', 'products.configurator_set_id = configuratorSet.id')
            ->innerJoin('configuratorSet', 's_article_configurator_set_group_relations', 'groupRelation', 'groupRelation.set_id = configuratorSet.id')
            ->innerJoin('groupRelation', 's_article_configurator_groups', 'configuratorGroup', 'configuratorGroup.id = groupRelation.group_id')
            ->innerJoin('configuratorSet', 's_article_configurator_set_option_relations', 'optionRelation', 'optionRelation.set_id = configuratorSet.id')
            ->innerJoin('optionRelation', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.id = optionRelation.option_id AND configuratorOption.group_id = configuratorGroup.id')
            ->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id')
            ->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id')
            ->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.name')
            ->addOrderBy('configuratorOption.position')
            ->addOrderBy('configuratorOption.name')
            ->groupBy('configuratorOption.id');

        return $query;
    }
}
