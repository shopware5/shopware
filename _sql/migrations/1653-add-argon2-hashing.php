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

use Shopware\Components\Migrations\AbstractMigration;

// see rfc for argon2 in PHP -> https://wiki.php.net/rfc/argon2_password_hash
if (!defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST')) {
    define('PASSWORD_ARGON2_DEFAULT_MEMORY_COST', 1 << 20); // 1MiB
}

if (!defined('PASSWORD_ARGON2_DEFAULT_TIME_COST')) {
    define('PASSWORD_ARGON2_DEFAULT_TIME_COST', 2);
}

if (!defined('PASSWORD_ARGON2_DEFAULT_THREADS')) {
    define('PASSWORD_ARGON2_DEFAULT_THREADS', 2);
}

class Migrations_Migration1653 extends AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @parentFormId = (SELECT id FROM s_core_config_forms WHERE NAME = "Passwörter" AND parent_id=(SELECT id FROM s_core_config_forms WHERE NAME="Core") LIMIT 1)');

        $sql = 'INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES ';
        $sql .= sprintf(
            "(@parentFormId, '%s', '%s', '%s', '%s', 'number', 1, 0, 0, '%s'),",
            'argon2MemoryCost',
            serialize(PASSWORD_ARGON2_DEFAULT_MEMORY_COST),
            'Argon2-Speicher',
            'Ein höherer Speicherverbrauch macht es einem möglichen Angreifer schwerer, ein passendes Klartext-Passwort zu erzeugen.',
            serialize(['minValue' => (string) (1 << 20), 'maxValue' => (string) (1 << 62)])
        );
        $sql .= sprintf(
            "(@parentFormId, '%s', '%s', '%s', '%s', 'number', 1, 0, 0, '%s'),",
            'argon2TimeCost',
            serialize(PASSWORD_ARGON2_DEFAULT_TIME_COST),
            'Argon2-Zeit',
            'Ein höherer Zeitaufwand macht es einem möglichen Angreifer schwerer, ein passendes Klartext-Passwort zu erzeugen.',
            serialize(['minValue' => '1', 'maxValue' => '30'])
        );
        $sql .= sprintf(
            "(@parentFormId, '%s', '%s', '%s', '%s', 'number', 1, 0, 0, '%s');",
            'argon2Threads',
            serialize(PASSWORD_ARGON2_DEFAULT_THREADS),
            'Argon2-Threads',
            'Anzahl paralleler Threads zur Erzeugung nutzen',
            serialize(['minValue' => '1', 'maxValue' => '32'])
        );

        $this->addSql($sql);

        // translation
        $this->addSql('SET @elemArgon2MemoryCost = (SELECT id FROM s_core_config_elements WHERE name="argon2MemoryCost" AND form_id = @parentFormId LIMIT 1)');
        $this->addSql('SET @elemargon2TimeCost = (SELECT id FROM s_core_config_elements WHERE name="argon2TimeCost" AND form_id = @parentFormId LIMIT 1)');
        $this->addSql('SET @elemargon2Threads = (SELECT id FROM s_core_config_elements WHERE name="argon2Threads" AND form_id = @parentFormId  LIMIT 1)');

        $sql = 'INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES ';
        // memory
        $sql .= sprintf(
            "(@elemArgon2MemoryCost, 2, '%s', '%s'),",
            'Argon2 memory',
            'Higher memory usage increases the security against attackers.'
        );

        // time
        $sql .= sprintf(
            "(@elemargon2TimeCost, 2, '%s', '%s'),",
            'Argon2 time',
            'Increasing the required time for hash calculation.'
        );

        // threads
        $sql .= sprintf(
            "(@elemargon2Threads, 2, '%s', '%s');",
            'Argon2 threads',
            'Use more threads for parallelism and therefore increased security against attackers, based on your setup.'
        );

        $this->addSql($sql);

        $this->addSql('UPDATE s_core_config_elements SET description = "Beachte, dass manche Hashfunktionen nur angezeigt werden, wenn die dafür benötigte PHP-Version installiert ist<br>Wenn \“Auto\” gewählt ist, wird bcrypt verwendet. Sollte bcrypt nicht verfügbar sein, wird sha256 verwendet." WHERE name = "defaultPasswordEncoder" AND form_id = @parentFormId');
        $this->addSql('UPDATE s_core_config_element_translations SET description = "Note that some hashing functions are only displayed if the required PHP version is installed <br>If \"Auto\" is selected, bcrypt is used. If bcrypt is not available, sha256 is used." WHERE element_id = (SELECT id FROM s_core_config_elements WHERE name = "defaultPasswordEncoder" AND form_id = @parentFormId)');
    }
}
