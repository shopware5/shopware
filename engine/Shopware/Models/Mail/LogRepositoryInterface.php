<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Mail;

use DateTimeInterface;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Log>
 */
interface LogRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * The findByDate method returns all log entries created between $since and $until.
     * If either of the parameters is left out, the default constant MIN_DATE (since) or MAX_DATE (until)
     * is used.
     */
    public function findByDate(?DateTimeInterface $since, ?DateTimeInterface $until): array;

    /**
     * The deleteByDate method removes all log entries created between $since and $until.
     * If either of the parameters is left out, the default constant MIN_DATE (since) or MAX_DATE (until)
     * is used.
     *
     * @return int|mixed
     */
    public function deleteByDate(?DateTimeInterface $since, ?DateTimeInterface $until);

    public function getFindByDateQueryBuilder(?DateTimeInterface $since, ?DateTimeInterface $until): QueryBuilder;

    public function getDeleteByDateQueryBuilder(?DateTimeInterface $since, ?DateTimeInterface $until): QueryBuilder;
}
