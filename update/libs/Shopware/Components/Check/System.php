<?php
class Shopware_Components_Check_System implements IteratorAggregate, Countable
{
    protected $list;

    /**
     * Returns the check list
     *
     * @return array
     */
    public function getList()
    {
        if ($this->list === null) {
            $list = simplexml_load_file(dirname(__FILE__) . '/System.xml');
            $this->list = array();
            if(!empty($list->requirement))
            foreach ($list->requirement as $requirement) {
                $name = (string)$requirement->name;
                $version = $this->check($name);
                $this->list[] = new ArrayObject(array(
                    'name' => $name,
                    'version' => $version,
                    'required' => (string)$requirement->required,
                    'group' => (string)$requirement->group,
                    'notice' => (string)$requirement->notice,
                    'error' => false,
                    'result' => $this->compare(
                        $name,
                        $version,
                        (string)$requirement->required
                    ),
                ), ArrayObject::ARRAY_AS_PROPS);
            }
        }
        return $this->list;
    }

    /**
     * Checks a requirement
     *
     * @param   string $name
     * @return  bool|null
     */
    protected function check($name)
    {
        $m = 'check' . str_replace(' ', '', ucwords(str_replace(array('_', '.'), ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m();
        } elseif (extension_loaded($name)) {
            return true;
            //return phpversion($name) ? phpversion($name) : true;
        } elseif (function_exists($name)) {
            return true;
        } elseif (($value = ini_get($name)) !== null) {
            if (strtolower($value) == 'off' || $value == 0) {
                return false;
            } elseif (strtolower($value) == 'on' || $value == 1) {
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
     * @param string $name
     * @param string $version
     * @param string $required
     * @return bool
     */
    protected function compare($name, $version, $required)
    {
        $m = 'compare' . str_replace(' ', '', ucwords(str_replace(array('_', '.'), ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m($version, $required);
        } elseif (preg_match('#^[0-9]+[A-Z]$#', $required)) {
            return $this->decodePhpSize($required) <= $this->decodePhpSize($version);
        } elseif (preg_match('#^[0-9]+ [A-Z]+$#i', $required)) {
            return $this->decodeSize($required) <= $this->decodeSize($version);
        } elseif (preg_match('#^[0-9][0-9\.]+$#', $required)) {
            return version_compare($required, $version, '<=');
        } else {
            return $required == $version;
        }
    }

    /**
     * Returns the check list
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getList());
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
        ob_start();
        phpinfo(1);
        $s = ob_get_contents();
        ob_end_clean();
        if (preg_match('/ionCube&nbsp;PHP&nbsp;Loader&nbsp;v([0-9.]+)/', $s, $match)) {
            return $match[1];
        }
        return false;
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
            return (bool)session_save_path();
        } elseif (ini_get('session.save_path')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks the magic quotes config
     *
     * @return bool|string
     */
    public function checkMagicQuotes()
    {
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            return true;
        } elseif (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) {
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
     * @return bool
     */
    public function checkIncludePath()
    {
        if (function_exists('set_include_path')) {
            $old = set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR);
            return $old && get_include_path() != $old;
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
        $last = strtolower($val[strlen($val) - 1]);
        $val = (float)$val;
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
        $val = (float)$val;
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
                $val = (float)$val;
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
        $types = array('B', 'KB', 'MB', 'GB', 'TB');
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
        $list = array();
        foreach ($this->getList() as $requirement) {
            $listResult = array();
            $listResult['name'] = (string)$requirement->name;
            $listResult['notice'] = (string)$requirement->notice;
            $listResult['required'] = (string)$requirement->required;
            $listResult['version'] = (string)$requirement->version;
            $listResult['result'] = (bool)(string)$requirement->result;
            $listResult['error'] = (bool)$requirement->error;
            if(!$listResult['result'] && $listResult['error']) {
                //$this->setFatalError(true);
            }
            $list[] = $listResult;
        }
        return $list;
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