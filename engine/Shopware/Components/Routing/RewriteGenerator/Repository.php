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

namespace Shopware\Components\Routing\RewriteGenerator;

use Doctrine\DBAL\Connection;

class Repository implements RepositoryInterface
{
    /**
     * @var QueryBuilderHelperInterface
     */
    private $helper;

    public function __construct(QueryBuilderHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function rewriteList(array $list, int $shopId): array
    {
        $query = $this->helper->getQueryBuilder();
        $query->setParameter('shopId', $shopId, \PDO::PARAM_INT);
        $query->setParameter('orgPath', $list, Connection::PARAM_STR_ARRAY);
        $statement = $query->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($list as $key => $orgPath) {
            if (isset($rows[$orgPath])) {
                $list[$key] = $rows[$orgPath];
            } else {
                $list[$key] = false;
            }
        }

        return $list;
    }
}
