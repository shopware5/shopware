<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @package Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 */
class ManufacturerGateway implements Gateway\ManufacturerGatewayInterface
{
    /**
     * @var Hydrator\ManufacturerHydrator
     */
    private $manufacturerHydrator;

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
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\ManufacturerHydrator $manufacturerHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\ManufacturerHydrator $manufacturerHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->fieldHelper = $fieldHelper;
    }


    /**
     * @inheritdoc
     */
    public function get($id, Struct\Context $context)
    {
        $manufacturers = $this->getList(array($id), $context);

        return array_shift($manufacturers);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $ids, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->fieldHelper->getManufacturerFields());

        $query->from('s_articles_supplier', 'manufacturer')
            ->leftJoin(
                'manufacturer',
                's_articles_supplier_attributes',
                'manufacturerAttribute',
                'manufacturerAttribute.supplierID = manufacturer.id'
            );

        $query->where('manufacturer.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addManufacturerTranslation($query);
        $query->setParameter(':language', $context->getShop()->getId());


        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $manufacturers = array();
        foreach ($data as $row) {
            $id = $row['__manufacturer_id'];
            $manufacturers[$id] = $this->manufacturerHydrator->hydrate($row);
        }

        return $manufacturers;
    }
}
