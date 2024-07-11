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

namespace Shopware\Components\Migrations;

use PDO;
use ReflectionClass;

abstract class AbstractMigration
{
    public const MODUS_UPDATE = 'update';
    public const MODUS_INSTALL = 'install';

    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var array<string>
     */
    protected $sql = [];

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param PDO $connection
     *
     * @return AbstractMigration
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getLabel(): string
    {
        $result = [];

        $regexPattern = '/[\d]*-(.+)\.php$/i';

        $rc = new ReflectionClass(\get_class($this));
        $fileName = basename((string) $rc->getFileName());

        preg_match($regexPattern, $fileName, $result);

        return (string) ($result[1] ?? '');
    }

    public function getVersion(): int
    {
        $result = [];
        $regexPattern = '/[\d]*$/';

        preg_match($regexPattern, \get_class($this), $result);

        return (int) ($result[0] ?? 0);
    }

    /**
     * @param self::MODUS_* $modus
     *
     * @return void
     */
    abstract public function up($modus);

    /**
     * @param string $sql
     */
    public function addSql($sql): AbstractMigration
    {
        // assure statement ends with semicolon
        $sql = rtrim($sql, ';');

        $this->sql[] = $sql;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getSql(): array
    {
        return $this->sql;
    }
}
