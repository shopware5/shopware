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

/**
 * Shopware Check System
 * <code>
 * $list = new Shopware_Components_Check_System();
 * $data = $list->toArray();
 * </code>
 */
class Shopware_Components_Check_System implements IteratorAggregate, Countable
{
    protected $list;

    /**
     * Checks all requirements
     */
    protected function checkAll()
    {
        foreach ($this->list as $requirement) {
            $requirement->version = $this->check($requirement->name);
            $requirement->result = $this->compare(
                $requirement->name,
                $requirement->version,
                $requirement->required
            );
        }
    }

    /**
     * Returns the check list
     *
     * @return Iterator
     */
    public function getList()
    {
        if ($this->list === null) {
            $this->list = new Zend_Config_Xml(
                dirname(__FILE__) . '/Data/System.xml',
                'requirements',
                true
            );
            $this->list = $this->list->requirement;
            $this->checkAll();
        }
        return $this->list;
    }

    /**
     * Checks a requirement
     *
     * @param string $name
     * @return bool|null
     */
    protected function check($name)
    {
        $m = 'check'.str_replace(' ', '', ucwords(str_replace(array('_', '.'), ' ', $name)));
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
            } else {
                return $value;
            }
        } else {
            return null;
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
     * Compares the requirement with the version
     *
     * @param string $name
     * @param string $version
     * @param string $required
     * @return bool
     */
    protected function compare($name, $version, $required)
    {
        $m = 'compare'.str_replace(' ', '', ucwords(str_replace(array('_', '.'), ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m($version, $required);
        } elseif (preg_match('#^[0-9]+[A-Z]$#', $required)) {
            return $this->decodePhpSize($required)<=$this->decodePhpSize($version);
        } elseif (preg_match('#^[0-9]+ [A-Z]+$#i', $required)) {
            return $this->decodeSize($required)<=$this->decodeSize($version);
        } elseif (preg_match('#^[0-9][0-9\.]+$#', $required)) {
            return version_compare($required, $version, '<=');
        } else {
            return $required==$version;
        }
    }

    /**
     * Returns the check list
     *
     * @return Iterator
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

    public function checkMysqlStrictMode()
    {
        try {
            $sql = "SELECT @@SESSION.sql_mode;";
            $result = Shopware()->Db()->query($sql)->fetchColumn(0);
            if (strpos($result, 'STRICT_TRANS_TABLES') !== false || strpos($result, 'STRICT_ALL_TABLES') !== false) {
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
     * @return bool|string
     */
    public function checkMysql()
    {
        if (Shopware()->Db()) {
            $v = Shopware()->Db()->getConnection()->getAttribute(Zend_Db::ATTR_SERVER_VERSION);
            if (strpos($v, '-')) {
                return substr($v, 0, strpos($v, '-'));
            } else {
                return $v;
            }
        }
        return false;
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
                if (substr_count($match[0], '.')==1) {
                    $match[0] .='.0';
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
            return !empty($gd['JPEG Support'])||!empty($gd['JPG Support']);
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
            return $this->encodeSize(disk_free_space(dirname(__FILE__)));
        } else {
            return false;
        }
    }

    /**
     * Checks the include path config
     *
     * @return unknown
     */
    public function checkIncludePath()
    {
        if (function_exists('set_include_path')) {
            $old = set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).DIRECTORY_SEPARATOR);
            return $old && get_include_path()!=$old;
        } else {
            return false;
        }
    }

    /**
     * Compare max execution time config
     *
     * @param string $version
     * @param string $required
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
     * @param string $val
     * @return float
     */
    public static function decodePhpSize($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (float) $val;
        switch ($last) {
            case 'g':
                $val *= 1024;
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
     * @return float
     */
    public static function decodeSize($val)
    {
        $val = trim($val);
        list($val, $last) = explode(' ', $val);
        $val = (float) $val;
        switch (strtoupper($last)) {
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
     * @return string
     */
    public static function encodeSize($bytes)
    {
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for ($i = 0; $bytes >= 1024 && $i < (count($types) -1); $bytes /= 1024, $i++);
        return(round($bytes, 2) . ' ' . $types[$i]);
    }

    /**
     *  Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getList()->toArray();
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
}
