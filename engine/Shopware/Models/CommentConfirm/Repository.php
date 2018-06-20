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

namespace Shopware\Models\CommentConfirm;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the CommentConfirm model (Shopware\Models\CommentConfirm\CommentConfirm).
 * <br>
 * The CommentConfirm model repository is responsible to manage all data in s_core_optin
 * This repository can be used to work with the saved optin data.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the blog article for the detail page
     *
     * @param string $hash
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfirmationByHashQuery($hash)
    {
        $builder = $this->getConfirmationByHashBuilder($hash);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfirmationByIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $hash
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfirmationByHashBuilder($hash)
    {
        $builder = $this->createQueryBuilder('commentConfirmation');
        $builder->select(['commentConfirmation'])
                ->where('commentConfirmation.hash = :hash')
                ->setParameter('hash', $hash);

        return $builder;
    }
}
