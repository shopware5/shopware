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

namespace Shopware\Models\Mail;

use Shopware\Components\Model\ModelRepository;

/**
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of all mails.
     * @param $mailId
     * @return \Doctrine\ORM\Query
     */
    public function getMailQuery($mailId)
    {
        $builder = $this->getMailQueryBuilder($mailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $mailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMailQueryBuilder($mailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('mails', 'attribute'))
                ->from('Shopware\Models\Mail\Mail', 'mails')
                ->leftJoin('mails.attribute', 'attribute')
                ->where('mails.id = ?1')
                ->setParameter(1, $mailId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attribute model
     * for the passed mail id.
     * @param $mailId
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($mailId)
    {
        $builder = $this->getAttributesQueryBuilder($mailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $mailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($mailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Mail', 'attribute')
                      ->where('attribute.mailId = ?1')
                      ->setParameter(1, $mailId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for mails with
     * the passed name. The passed mail id will be excluded.
     *
     * @param $name
     * @param $mailId
     * @return \Doctrine\ORM\Query
     */
    public function getValidateNameQuery($name, $mailId = null)
    {
        $builder = $this->getValidateNameQueryBuilder($name, $mailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateNameQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param      $name
     * @param null $mailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateNameQueryBuilder($name, $mailId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('mail'))
                ->from('Shopware\Models\Mail\Mail', 'mail')
                ->where('mail.name = :name')
                ->setParameter('name', $name);

        if ($mailId) {
            $builder->andWhere('mail.id != :id');
            $builder->setParameter('id', $mailId);
        }

        return $builder;
    }
}
