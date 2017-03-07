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

namespace Shopware\Components\Check;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Requirements
{
    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @param string $sourceFile
     * @param \PDO   $connection
     */
    public function __construct($sourceFile, $connection)
    {
        if (!is_readable($sourceFile)) {
            throw new \RuntimeException(sprintf('Cannot read requirements file in %s.', $sourceFile));
        }

        $this->sourceFile = $sourceFile;
        $this->connection = $connection;
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

        foreach ($this->runChecks() as $requirement) {
            $check = [];
            $check['name'] = (string) $requirement->name;
            $check['group'] = (string) $requirement->group;
            $check['notice'] = (string) $requirement->notice;
            $check['required'] = (string) $requirement->required;
            $check['version'] = (string) $requirement->version;
            $check['check'] = (bool) (string) $requirement->result;
            $check['result'] = (bool) $requirement->result;
            $check['error'] = (bool) $requirement->error;

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
     *
     * @return \SimpleXMLElement[]
     */
    private function runChecks()
    {
        $xmlObject = simplexml_load_file($this->sourceFile);

        if (!is_object($xmlObject->requirements)) {
            throw new \RuntimeException('Requirements XML file is not valid.');
        }

        foreach ($xmlObject->requirement as $requirement) {
            $name = (string) $requirement->name;
            $value = $this->getRuntimeValue($name);
            $requirement->result = $this->compare(
                $name,
                $value,
                (string) $requirement->required
            );
            $requirement->version = $value;
        }

        return $xmlObject->requirement;
    }

    /**
     * Checks a requirement
     *
     * @param string $name
     *
     * @return bool|string|int|null
     */
    private function getRuntimeValue($name)
    {
        $m = 'check' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m();
        } elseif (extension_loaded($name)) {
            return true;
        } elseif (function_exists($name)) {
            return true;
        } elseif (($value = ini_get($name)) !== null) {
            if (strtolower($value) == 'off' || (is_numeric($value) && $value == 0)) {
                return false;
            } elseif (strtolower($value) == 'on' || (is_numeric($value) && $value == 1)) {
                return true;
            }

            return $value;
        }

        return null;
    }

    /**
     * Compares the requirement with the version
     *
     * @param string $name
     * @param string $value
     * @param string $requiredValue
     *
     * @return bool
     */
    private function compare($name, $value, $requiredValue)
    {
        $m = 'compare' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m($value, $requiredValue);
        } elseif (preg_match('#^[0-9]+[A-Z]$#', $requiredValue)) {
            return $this->decodePhpSize($requiredValue) <= $this->decodePhpSize($value);
        } elseif (preg_match('#^[0-9]+ [A-Z]+$#i', $requiredValue)) {
            return $this->decodeSize($requiredValue) <= $this->decodeSize($value);
        } elseif (preg_match('#^[0-9][0-9\.]+$#', $requiredValue)) {
            return version_compare($requiredValue, $value, '<=');
        }

        return $requiredValue == $value;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the ion cube loader
     *
     * @return bool|string
     */
    private function checkIonCubeLoader()
    {
        if (!extension_loaded('ionCube Loader')) {
            return false;
        }

        if (!function_exists('ioncube_loader_version')) {
            return false;
        }

        return ioncube_loader_version();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the php version
     *
     * @return bool|string
     */
    private function checkPhp()
    {
        if (strpos(phpversion(), '-')) {
            return substr(phpversion(), 0, strpos(phpversion(), '-'));
        }

        return phpversion();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function checkMysqlStrictMode()
    {
        try {
            $sql = 'SELECT @@SESSION.sql_mode;';
            $result = $this->connection->query($sql)->fetchColumn(0);
            if (strpos($result, 'STRICT_TRANS_TABLES') !== false || strpos($result, 'STRICT_ALL_TABLES') !== false) {
                return true;
            }
        } catch (\PDOException $e) {
            return true;
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the mysql version
     *
     * @return bool|string
     */
    private function checkMysql()
    {
        $v = $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
        if (strpos($v, '-')) {
            return substr($v, 0, strpos($v, '-'));
        }

        return $v;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the curl version
     *
     * @return bool|string
     */
    private function checkCurl()
    {
        if (function_exists('curl_version')) {
            $curl = curl_version();

            return $curl['version'];
        } elseif (function_exists('curl_init')) {
            return true;
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the lib xml version
     *
     * @return bool|string
     */
    private function checkLibXml()
    {
        if (defined('LIBXML_DOTTED_VERSION')) {
            return LIBXML_DOTTED_VERSION;
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the gd version
     *
     * @return bool|string
     */
    private function checkGd()
    {
        if (function_exists('gd_info')) {
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

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the gd jpg support
     *
     * @return bool|string
     */
    private function checkGdJpg()
    {
        if (function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['JPEG Support']) || !empty($gd['JPG Support']);
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the freetype support
     *
     * @return bool|string
     */
    private function checkFreetype()
    {
        if (function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['FreeType Support']);
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the session save path config
     *
     * @return bool|string
     */
    private function checkSessionSavePath()
    {
        if (function_exists('session_save_path')) {
            return (bool) session_save_path();
        } elseif (ini_get('session.save_path')) {
            return true;
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the disk free space
     *
     * @return bool|string
     */
    private function checkDiskFreeSpace()
    {
        if (function_exists('disk_free_space')) {
            // Prevent Warning: disk_free_space() [function.disk-free-space]: Value too large for defined data type
            $freeSpace = @disk_free_space(__DIR__);

            return $this->encodeSize($freeSpace);
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the suhosin.get.max_value_length which limits the max get parameter length.
     *
     * @return int
     */
    private function checkSuhosinGetMaxValueLength()
    {
        $length = (int) ini_get('suhosin.get.max_value_length');
        if ($length === 0) {
            return 2000;
        }

        return $length;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Checks the include path config
     *
     * @return bool
     */
    private function checkIncludePath()
    {
        if (function_exists('set_include_path')) {
            $old = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR);

            return $old && get_include_path() != $old;
        }

        return false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Compare max execution time config
     *
     * @param string $version
     * @param string $required
     *
     * @return bool
     */
    private function compareMaxExecutionTime($version, $required)
    {
        if (!$version) {
            return true;
        }

        return version_compare($required, $version, '<=');
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */

    /**
     * Decode php size format
     *
     * @param string $val
     *
     * @return float
     */
    private function decodePhpSize($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (float) $val;
        switch ($last) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Decode byte size format
     *
     * @param string $val
     *
     * @return float
     */
    private function decodeSize($val)
    {
        $val = trim($val);
        list($val, $last) = explode(' ', $val);
        $val = (float) $val;
        switch (strtoupper($last)) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'TB':
                $val *= 1024;
            case 'GB':
                $val *= 1024;
            case 'MB':
                $val *= 1024;
            case 'KB':
                $val *= 1024;
            case 'B':
                $val = (float) $val;
        }

        return $val;
    }

    /**
     * Encode byte size format
     *
     * @param float $bytes
     *
     * @return string
     */
    private function encodeSize($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);

        return round($bytes, 2) . ' ' . $types[$i];
    }
}
