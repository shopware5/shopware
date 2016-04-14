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

namespace Shopware\Components;

use Shopware\Components\Model\ModelManager;

/**
 * @category  Shopware
 * @package   Shopware\Components\NumberRangeManager
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class NumberRangeManager
{
    /**
     * @var ModelManager The entity manager
     */
    private $em;

    /**
     * @param ModelManager $em
     */
    public function __construct(ModelManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $name
     * @return int
     * @throws \Exception
     */
    public function getCurrentNumber($name)
    {
        $numberRange = $this->em->getRepository('Shopware\Models\Order\Number')->findOneBy([
            'name' => $name
        ]);
        if (!$numberRange) {
            throw new \Exception('Number range with name "' . $name . '" does not exist.');
        }

        return $numberRange->getNumber();
    }

    /**
     * Fetches the number range with the given name and increases its value by one to the get
     * the next number, which is than written back to the database. The fetch and update are performed
     * within a transaction using 'locking reads' to block all other transactions from overwriting the
     * value until the update is committed. Finally the next number is returned.
     *
     * @param string $name
     * @return int
     * @throws \Exception
     */
    public function getNextNumber($name)
    {
        // Begin a new transaction
        $this->em->getConnection()->beginTransaction();
        try {
            // Select the number range with the given name using the 'PESSIMISTIC_WRITE' lock mode,
            // which results in a 'SELECT ... FOR UPDATE' query
            $builder = $this->em->createQueryBuilder();
            $builder->select('numberRange')
                ->from('Shopware\Models\Order\Number', 'numberRange')
                ->where('numberRange.name = :name')
                ->setParameter('name', $name);
            $query = $builder->getQuery();
            $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            $numberRange = $query->getOneOrNullResult();
            if (!$numberRange) {
                $this->em->getConnection()->rollBack();
                throw new \Exception(sprintf('Number range with name "%s" does not exist.', $name));
            }

            // Increase the number by one and write it back to the database
            $nextNumber = $numberRange->getNumber() + 1;
            $numberRange->setNumber($nextNumber);
            $this->em->flush($numberRange);
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $nextNumber;
    }
}
