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

namespace Shopware\Components\Check;

use ErrorException;
use PDO;
use PDOException;
use RuntimeException;
use Shopware_Components_Snippet_Manager;
use SimpleXMLElement;

class Requirements
{
    private string $sourceFile;

    private PDO $connection;

    private Shopware_Components_Snippet_Manager $snippetManager;

    public function __construct(string $sourceFile, PDO $connection, Shopware_Components_Snippet_Manager $snippetManager)
    {
        if (!is_readable($sourceFile)) {
            throw new RuntimeException(sprintf('Cannot read requirements file in %s.', $sourceFile));
        }

        $this->sourceFile = $sourceFile;
        $this->connection = $connection;
        $this->snippetManager = $snippetManager;
    }

    /**
     *  Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            'hasErrors' => false,
            'hasWarnings' => false,
            'checks' => [],
        ];

        $checks = [];
        foreach ($this->runChecks() as $requirement) {
            $check = [];
            $check['name'] = (string) $requirement->name;
            $check['group'] = (string) $requirement->group;
            $check['notice'] = (string) $requirement->notice;
            $check['required'] = (string) $requirement->required;
            $check['version'] = (string) $requirement->version;
            $check['maxCompatibleVersion'] = (string) $requirement->maxCompatibleVersion;
            $check['check'] = (bool) (string) $requirement->result;
            $check['result'] = (bool) $requirement->result;
            $check['error'] = (bool) $requirement->error;

            if ($check['maxCompatibleVersion'] && $check['check']) {
                $check = $this->handleMaxCompatibleVersion($check);
            }

            $checks[] = $check;
        }

        $checks = array_merge($checks, $this->checkOpcache());

        foreach ($checks as $check) {
            if (!$check['check'] && $check['error']) {
                $check['status'] = 'error';
                $result['hasErrors'] = true;
            } elseif (!$check['check']) {
                $check['status'] = 'warning';
                $result['hasWarnings'] = true;
            } else {
                $check['status'] = 'ok';
            }
            unset($check['check'], $check['error']);

            $result['checks'][] = $check;
        }

        return $result;
    }

    /**
     * Returns the check list
     */
    private function runChecks(): SimpleXMLElement
    {
        $xmlObject = simplexml_load_string(file_get_contents($this->sourceFile));

        if (!\is_object($xmlObject->requirements)) {
            throw new RuntimeException('Requirements XML file is not valid.');
        }

        foreach ($xmlObject->requirement as $requirement) {
            $name = (string) $requirement->name;

            if ($name === 'database') {
                [$platform, $version] = $this->getMysqlVersion();

                $requirement->version = $version;
                $requireVersion = (string) $platform === 'mysql' ? $requirement->mysql : $requirement->mariadb;
                $requirement->result = version_compare($version, $requireVersion, '>=');
                $requirement->required = $requireVersion;
                $requirement->name = $platform;
            } else {
                $value = (string) $this->getRuntimeValue($name, $requirement);
                $requirement->result = $this->compare(
                    $name,
                    $value,
                    (string) $requirement->required
                );
                $requirement->version = $value;
            }
        }

        return $xmlObject->requirement;
    }

    /**
     * Checks a requirement
     *
     * @return bool|string|int|null
     */
    private function getRuntimeValue(string $name, SimpleXMLElement $requirement)
    {
        $m = 'check' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m($requirement);
        }

        if (\extension_loaded($name)) {
            return true;
        }

        if (\function_exists($name)) {
            return true;
        }

        $value = \ini_get($name);
        if ($value !== '' && $value !== false) {
            if ((int) $value === 0 || strtolower($value) === 'off') {
                return false;
            }

            if ((int) $value === 1 || strtolower($value) === 'on') {
                return true;
            }

            return $value;
        }

        return null;
    }

    /**
     * Compares the requirement with the version
     */
    private function compare(string $name, string $value, string $requiredValue): bool
    {
        $m = 'compare' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));

        if (method_exists($this, $m)) {
            return $this->$m($value, $requiredValue);
        }

        if (preg_match('#^[0-9]+[A-Z]$#', $requiredValue)) {
            return $this->decodePhpSize($requiredValue) <= $this->decodePhpSize($value);
        }

        if (preg_match('#^[0-9]+ [A-Z]+$#i', $requiredValue)) {
            return $this->decodeSize($requiredValue) <= $this->decodeSize($value);
        }

        if (preg_match('#^[0-9][0-9\.]+$#', $requiredValue)) {
            return version_compare($requiredValue, $value, '<=');
        }

        return $requiredValue == $value;
    }

    /**
     * Checks the php version
     */
    private function checkPhp(): string
    {
        $phpVersionDashPosition = strpos(PHP_VERSION, '-');
        if ($phpVersionDashPosition !== false) {
            return substr(PHP_VERSION, 0, $phpVersionDashPosition);
        }

        return PHP_VERSION;
    }

    private function checkMysqlStrictMode(): bool
    {
        try {
            $sql = 'SELECT @@SESSION.sql_mode;';
            $result = $this->connection->query($sql)->fetchColumn();
            if (\is_string($result) && (strpos($result, 'STRICT_TRANS_TABLES') !== false || strpos($result, 'STRICT_ALL_TABLES') !== false)) {
                return true;
            }
        } catch (PDOException $e) {
            return true;
        }

        return false;
    }

    /**
     * Checks the mysql version
     *
     * @return array<int, string>
     */
    private function getMysqlVersion(): array
    {
        $v = $this->connection->query('SELECT VERSION()')->fetchColumn();

        return MySQLVersionExtractor::extract($v);
    }

    /**
     * Checks the opcache configuration if the opcache exists.
     *
     * @return array<int, array<string, string|int|bool>>
     */
    private function checkOpcache(): array
    {
        if (!\extension_loaded('Zend OPcache')) {
            return [];
        }

        $useCwdOption = $this->compare('opcache.use_cwd', \ini_get('opcache.use_cwd'), '1');
        $opcacheRequirements = [[
            'name' => 'opcache.use_cwd',
            'group' => 'core',
            'required' => 1,
            'version' => \ini_get('opcache.use_cwd'),
            'result' => \ini_get('opcache.use_cwd'),
            'notice' => '',
            'check' => $this->compare('opcache.use_cwd', \ini_get('opcache.use_cwd'), '1'),
            'error' => '',
        ]];

        try {
            if (fileinode('/') > 2) {
                $validateRootOption = $this->compare('opcache.validate_root', \ini_get('opcache.validate_root'), '1');
                $opcacheRequirements[] = [
                    'name' => 'opcache.validate_root',
                    'group' => 'core',
                    'required' => 1,
                    'version' => \ini_get('opcache.validate_root'),
                    'result' => \ini_get('opcache.validate_root'),
                    'notice' => '',
                    'check' => $this->compare('opcache.validate_root', \ini_get('opcache.validate_root'), '1'),
                    'error' => '',
                ];
            }
        } catch (ErrorException $x) {
            // Systems that have an 'open_basedir' defined might not allow an access of '/'
        }

        return $opcacheRequirements;
    }

    /**
     * Checks the curl version
     *
     * @return bool|string
     */
    private function checkCurl()
    {
        if (\function_exists('curl_version')) {
            $curl = curl_version();

            return $curl['version'];
        } elseif (\function_exists('curl_init')) {
            return true;
        }

        return false;
    }

    /**
     * Checks the lib xml version
     *
     * @return bool|string
     */
    private function checkLibXml()
    {
        if (\defined('LIBXML_DOTTED_VERSION')) {
            return LIBXML_DOTTED_VERSION;
        }

        return false;
    }

    /**
     * Checks the gd version
     *
     * @return bool|string
     */
    private function checkGd()
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();
            if (preg_match('#[0-9.]+#', $gd['GD Version'], $match)) {
                if (substr_count($match[0], '.') == 1) {
                    $match[0] .= '.0';
                }

                return $match[0];
            }

            return $gd['GD Version'];
        }

        return false;
    }

    /**
     * Checks the gd jpg support
     */
    private function checkGdJpg(): bool
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['JPEG Support']) || !empty($gd['JPG Support']);
        }

        return false;
    }

    /**
     * Checks the freetype support
     */
    private function checkFreetype(): bool
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['FreeType Support']);
        }

        return false;
    }

    /**
     * Checks the session save path config
     */
    private function checkSessionSavePath(): bool
    {
        if (\function_exists('session_save_path')) {
            return (bool) session_save_path();
        }

        if (\ini_get('session.save_path')) {
            return true;
        }

        return false;
    }

    /**
     * Checks the disk free space
     *
     * @return bool|string
     */
    private function checkDiskFreeSpace()
    {
        if (\function_exists('disk_free_space')) {
            // Prevent Warning: disk_free_space() [function.disk-free-space]: Value too large for defined data type
            $freeSpace = @disk_free_space(__DIR__);

            return $this->encodeSize($freeSpace);
        }

        return false;
    }

    /**
     * Checks the suhosin.get.max_value_length which limits the max get parameter length.
     */
    private function checkSuhosinGetMaxValueLength(): int
    {
        $length = (int) \ini_get('suhosin.get.max_value_length');
        if ($length === 0) {
            return 2000;
        }

        return $length;
    }

    /**
     * Checks the include path config
     */
    private function checkIncludePath(): bool
    {
        if (\function_exists('set_include_path')) {
            $old = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR);

            return $old && get_include_path() != $old;
        }

        return false;
    }

    /**
     * Compare max execution time config
     */
    private function compareMaxExecutionTime(string $version, string $required): bool
    {
        if (!$version) {
            return true;
        }

        return version_compare($required, $version, '<=');
    }

    /**
     * Decode php size format
     */
    private function decodePhpSize(string $val): float
    {
        $val = trim($val);
        $last = strtolower($val[\strlen($val) - 1]);
        $phpSize = (float) $val;
        switch ($last) {
            case 'g':
                $phpSize *= 1024;
                // no break
            case 'm':
                $phpSize *= 1024;
                // no break
            case 'k':
                $phpSize *= 1024;
        }

        return $phpSize;
    }

    /**
     * Decode byte size format
     */
    private function decodeSize(string $val): float
    {
        $val = trim($val);
        [$val, $last] = explode(' ', $val);
        $size = (float) $val;
        switch (strtoupper($last)) {
            case 'TB':
                $size *= 1024;
                // no break
            case 'GB':
                $size *= 1024;
                // no break
            case 'MB':
                $size *= 1024;
                // no break
            case 'KB':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Encode byte size format
     */
    private function encodeSize(float $bytes): string
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (\count($types) - 1); $bytes /= 1024, $i++) {
        }

        return round($bytes, 2) . ' ' . $types[$i];
    }

    private function handleMaxCompatibleVersion(array $check): array
    {
        if (version_compare($check['version'], $check['maxCompatibleVersion'], '>')) {
            $check['check'] = false;
            $maxCompatibleVersion = str_replace('.99', '', $check['maxCompatibleVersion']);
            $check['notice'] = sprintf($this->snippetManager->getNamespace('backend/systeminfo/view')->get('php_version_is_too_new_warning'), $maxCompatibleVersion);
        }

        return $check;
    }
}
