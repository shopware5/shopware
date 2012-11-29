<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;

/**
 * Variant API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Variant extends Resource
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Article\Detail');
    }


    /**
     * @param string $number
     * @return array|\Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->getOne($id);
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->getDetailsByIdsQuery(array($id));
        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $builder->getOneOrNullResult($this->getResultMode());

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }


        return $articleDetail;
    }


    /**
     * Little helper function for the ...ByNumber methods
     * @param $number
     * @return int
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $this->getRepository()->findOneBy(array('number' => $number));

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by number {$number} not found");
        }

        return $articleDetail->getId();
    }


    /**
     * @param string $number
     * @return \Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function deleteByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->delete($id);
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $this->getRepository()->find($id);

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }

        if ($articleDetail->getKind() === 1) {
            $articleDetail->getArticle()->setMainDetail(null);
        }

        $this->getManager()->remove($articleDetail);
        $this->flush();

        return $articleDetail;
    }
}
