<?php declare(strict_types=1);
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

namespace Shopware\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Components\Random;

class OptInLoginService implements OptInLoginServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function refreshOptInHashForUser(int $userId, int $optInId, \DateTimeInterface $lastSentDate): string
    {
        $hash = $this->getHashForOptInId($optInId, $lastSentDate);
        $this->updateOptInEntry($optInId, $hash);
        $this->updateUserSentDate($userId);

        return $hash;
    }

    protected function getHashForOptInId(int $optInId, \DateTimeInterface $lastSentDate): string
    {
        if ($this->isDateInLast15Minutes($lastSentDate)) {
            $sql = <<<'SQL'
                SELECT hash
                FROM s_core_optin
                WHERE id = :id;
SQL;

            return (string) $this->connection->fetchColumn($sql, [':id' => $optInId]);
        }

        return Random::getAlphanumericString(32);
    }

    protected function updateOptInEntry(int $optInId, string $newHash): void
    {
        $sql = <<<'SQL'
            UPDATE s_core_optin
            SET hash = :hash, type = 'swRegister', datum = NOW()
            WHERE id = :optInId
SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':hash', $newHash);
        $statement->bindParam(':optInId', $optInId);

        $statement->execute();
    }

    protected function updateUserSentDate(int $userId): void
    {
        $sql = <<<'SQL'
            UPDATE `s_user`
            SET doubleOptinEmailSentDate = NOW()
            WHERE id = :userId
SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':userId', $userId);

        $statement->execute();
    }

    private function isDateInLast15Minutes(\DateTimeInterface $lastSentDate): bool
    {
        $sentDateTimestamp = $lastSentDate->getTimestamp();
        $nowTimestamp = time();

        $sentDateTimestampPlus15Minutes = $sentDateTimestamp + (15 * 60);

        return $sentDateTimestampPlus15Minutes >= $nowTimestamp;
    }
}
