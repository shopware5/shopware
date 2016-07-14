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

namespace Shopware\Recovery\Install;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Update
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Requirements implements \IteratorAggregate, \Countable
{
    /**
     * @var
     */
    protected $list;

    /**
     * @var bool
     */
    protected $fatalError;

    /**
     * @var string
     */
    private $sourceFile;
    private $containsWarnings;

    /**
     * @param string $sourceFile
     */
    public function __construct($sourceFile)
    {
        $this->sourceFile = $sourceFile;
    }

    /**
     * Checks all requirements
     */
    protected function checkAll()
    {
        foreach ($this->list as $requirement) {
            $name = (string) $requirement->name;
            $value = $this->getRuntimeValue($name);

            $requirement->result = $this->compare(
                $name,
                $value,
                (string) $requirement->required
            );
            $requirement->version = $value;
        }
    }

    /**
     * Returns the check list
     *
     * @return \Iterator
     */
    public function getList()
    {
        if ($this->list === null) {
            $xml_object = simplexml_load_file($this->sourceFile);
            if (is_object($xml_object->requirements) == true) {
                $this->list = $xml_object->requirement;
            }

            $this->checkAll();
        }

        return $this->list;
    }

    /**
     * Checks a requirement
     *
     * @param  string                   $name
     * @return bool|string|integer|null
     */
    protected function getRuntimeValue($name)
    {
        $m = 'check' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m();
        } elseif (extension_loaded($name)) {
            return true;
            //return phpversion($name) ? phpversion($name) : true;
        } elseif (function_exists($name)) {
            return true;
        } elseif (($value = ini_get($name)) !== null) {
            if (strtolower($value) == 'off' || (is_numeric($value) && $value == 0)) {
                return false;
            } elseif (strtolower($value) == 'on' || (is_numeric($value) && $value == 1)) {
                return true;
            } else {
                return $value;
            }
        } else {
            return null;
        }
    }

    /**
     * Compares the requirement with the version
     *
     * @param  string $name
     * @param  string $value
     * @param  string $requiredValue
     * @return bool
     */
    protected function compare($name, $value, $requiredValue)
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
        } else {
            return $requiredValue == $value;
        }
    }

    /**
     * Returns the check list
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getList();
    }

    /**
     * Checks the ion cube loader
     *
     * @return bool|string
     */
    public function checkIonCubeLoader()
    {
        if (!extension_loaded('ionCube Loader')) {
            return false;
        }

        if (!function_exists('ioncube_loader_version')) {
            return false;
        }

        return ioncube_loader_version();
    }

    /**
     * Checks the php version
     *
     * @return bool|string
     */
    public function checkPhp()
    {
        if (strpos(phpversion(), '-')) {
            return substr(phpversion(), 0, strpos(phpversion(), '-'));
        } else {
            return phpversion();
        }
    }

    /**
     * Checks the php version
     *
     * @return bool
     */
    public function checkModRewrite()
    {
        return isset($_SERVER['MOD_REWRITE']);
    }

    /**
     * Checks the curl version
     *
     * @return bool|string
     */
    public function checkCurl()
    {
        if (function_exists('curl_version')) {
            $curl = curl_version();

            return $curl['version'];
        } elseif (function_exists('curl_init')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks the lib xml version
     *
     * @return bool|string
     */
    public function checkLibXml()
    {
        if (defined('LIBXML_DOTTED_VERSION')) {
            return LIBXML_DOTTED_VERSION;
        } else {
            return false;
        }
    }

    /**
     * Checks the gd version
     *
     * @return bool|string
     */
    public function checkGd()
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
        } else {
            return false;
        }
    }

    /**
     * Checks the gd jpg support
     *
     * @return bool|string
     */
    public function checkGdJpg()
    {
        if (function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['JPEG Support']) || !empty($gd['JPG Support']);
        } else {
            return false;
        }
    }

    /**
     * Checks the freetype support
     *
     * @return bool|string
     */
    public function checkFreetype()
    {
        if (function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['FreeType Support']);
        } else {
            return false;
        }
    }

    /**
     * Checks the session save path config
     *
     * @return bool|string
     */
    public function checkSessionSavePath()
    {
        if (function_exists('session_save_path')) {
            return (bool) session_save_path();
        } elseif (ini_get('session.save_path')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks the disk free space
     *
     * @return bool|string
     */
    public function checkDiskFreeSpace()
    {
        if (function_exists('disk_free_space')) {
            // Prevent Warning: disk_free_space() [function.disk-free-space]: Value too large for defined data type
            $freeSpace = @disk_free_space(__DIR__);

            return $this->encodeSize($freeSpace);
        } else {
            return false;
        }
    }

    /**
     * Checks the suhosin.get.max_value_length which limits the max get parameter length.
     *
     * @return int
     */
    public function checkSuhosinGetMaxValueLength()
    {
        $length = (int) ini_get('suhosin.get.max_value_length');
        if ($length === 0) {
            return 2000;
        } else {
            return $length;
        }
    }

    /**
     * Checks the include path config
     *
     * @return bool
     */
    public function checkIncludePath()
    {
        $old = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR);

        return $old && get_include_path() != $old;
    }

    /**
     * Compare max execution time config
     *
     * @param  string $version
     * @param  string $required
     * @return bool
     */
    public function compareMaxExecutionTime($version, $required)
    {
        if (!$version) {
            return true;
        }

        return version_compare($required, $version, '<=');
    }

    /**
     * Decode php size format
     *
     * @param  string $val
     * @return float
     */
    public static function decodePhpSize($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (float) $val;
        switch ($last) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
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
     * @param  string $val
     * @return float
     */
    public static function decodeSize($val)
    {
        $val = trim($val);
        list($val, $last) = explode(' ', $val);
        $val = (float) $val;
        switch (strtoupper($last)) {
            /** @noinspection PhpMissingBreakStatementInspection */
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
     * @param  float  $bytes
     * @return string
     */
    public static function encodeSize($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) ;

        return (round($bytes, 2) . ' ' . $types[$i]);
    }

    /**
     *  Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        $list = [];

        foreach ($this->getList() as $requirement) {
            $result = [];
            $result['name']     = (string) $requirement->name;
            $result['group']     = (string) $requirement->group;
            $result['notice']   = (string) $requirement->notice;
            $result['required'] = (string) $requirement->required;
            $result['version']  = (string) $requirement->version;
            $result['result']   = (bool) (string) $requirement->result;
            $result['error']    = (bool) $requirement->error;

            if (!$result['result'] && $result['error']) {
                $result['status'] = 'error';
                $this->setFatalError(true);
            } elseif (!$result['result']) {
                $this->setContainsWarnings(true);
                $result['status'] = 'warning';
            } else {
                $result['status'] = 'ok';
            }

            unset($result['result']);
            unset($result['error']);

            $list[] = $result;
//            $list[$result['group']][] = $result;
        }

        return $list;
    }

    public function setContainsWarnings($containsWarnings)
    {
        $this->containsWarnings = $containsWarnings;
    }

    /**
     * Counts the check list
     *
     * @return int
     */
    public function count()
    {
        return $this->getList()->count();
    }

    /**
     * @param bool $fatalError
     */
    public function setFatalError($fatalError)
    {
        $this->fatalError = (bool) $fatalError;
    }

    /**
     * @return bool
     */
    public function getFatalError()
    {
        return $this->fatalError;
    }

    /**
     * @return mixed
     */
    public function getContainsWarnings()
    {
        return $this->containsWarnings;
    }
}
