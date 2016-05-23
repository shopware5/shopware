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

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\AttributeBundle\Repository\RegistryInterface;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Shopware_Controllers_Backend_EntitySearch extends Shopware_Controllers_Backend_ExtJs
{
    public function searchAction()
    {
        try {
            $criteria = $this->createCriteria($this->Request());

            /** @var RegistryInterface $registry */
            $registry = $this->get('shopware_attribute.repository_registry');

            $repository = $registry->getRepository($criteria);
            $result = $repository->search($criteria);

            $this->View()->assign([
                'success' => true,
                'data' => array_values($result->getData()),
                'total' => $result->getCount()
            ]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => true, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return SearchCriteria
     */
    private function createCriteria(Request $request)
    {
        $criteria = new SearchCriteria($request->getParam('model'));
        $criteria->offset = $request->getParam('start', 0);
        $criteria->limit = $request->getParam('limit', 30);
        $criteria->ids = $request->getParam('ids', []);
        $criteria->term = $request->getParam('query', null);
        $criteria->sortings = $request->getParam('sorts', []);
        $criteria->conditions = $request->getParam('filters', []);
        $criteria->params = $request->getParams();

        if (!empty($criteria->ids)) {
            $criteria->ids = json_decode($criteria->ids, true);
        }

        return $criteria;
    }
}
