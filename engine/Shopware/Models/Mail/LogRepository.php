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

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelRepository;

class LogRepository extends ModelRepository implements LogRepositoryInterface
{
    public const MIN_DATE = '2019-01-01T00:00:00+0000';
    public const MAX_DATE = 'now';

    /**
     * @var DateTimeInterface
     */
    private $minDate;

    /**
     * @var DateTimeInterface
     */
    private $maxDate;

    public function __construct(EntityManagerInterface $entityManager, ClassMetadata $class)
    {
        $this->minDate = new DateTime(self::MIN_DATE);
        $this->maxDate = new DateTime(self::MAX_DATE);

        parent::__construct($entityManager, $class);
    }

    /**
     * {@inheritdoc}
     */
    public function findByDate(?DateTimeInterface $since, ?DateTimeInterface $until): array
    {
        return $this->getFindByDateQueryBuilder($since, $until)->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByDate(?DateTimeInterface $since, ?DateTimeInterface $until)
    {
        return $this->getDeleteByDateQueryBuilder($since, $until)->getQuery()->execute();
    }

    public function getFindByDateQueryBuilder(?DateTimeInterface $since, ?DateTimeInterface $until): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('log')
            ->from(Log::class, 'log');
        $this->addDateConstraint($qb, $since, $until);

        return $qb;
    }

    public function getDeleteByDateQueryBuilder(?DateTimeInterface $since, ?DateTimeInterface $until): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->delete(Log::class, 'log');
        $this->addDateConstraint($qb, $since, $until);

        return $qb;
    }

    protected function addDateConstraint(QueryBuilder $qb, ?DateTimeInterface $since, ?DateTimeInterface $until): void
    {
        $qb->andWhere('log.sentAt BETWEEN :since AND :until')
            ->setParameters([
                'since' => $since ?: $this->minDate,
                'until' => $until ?: $this->maxDate,
            ]);
    }
}
