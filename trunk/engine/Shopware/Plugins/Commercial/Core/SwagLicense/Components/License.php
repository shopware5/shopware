<?php
final class Shopware_Components_License
{
    private $rawList;

    private $list;

    private $license;

    private $host;

    /**
     * @param $host
     * @param $list
     */
    public function __construct($host, $list)
    {
        if($host !== null) {
            $this->setHost($host);
        }
        $this->setRawList($list);
        //$this->checkCoreLicense();
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getCoreLicense()
    {
        return $this->license;
    }

    /***
     * @param   bool $throwException
     * @return  bool
     * @throws  Exception
     */
    public function checkCoreLicense($throwException = true)
    {
        static $r;
        if(!isset($r)) {
//            $m = 'SwagShopCore'; $m = $this->getLicense($m);
            $l = $this->license; $h = $this->host;
            $s = base64_decode('VZ4nYpGmROGvHdDdGsD17XxR88M=');
            $r = $l === sha1($h . $s . $h, true);
//            if($r) {
//                $r = $m === sha1($l . $s . $l, true);
//            }
        }
        if(!$r && $throwException) {
            throw new Exception('No valid core license found.');
        }
        return $r;
    }

    /**
     * @param   $module
     * @param   null $host
     * @return  mixed
     */
    public function hasLicense($module, $host = null)
    {
        $host = $host === null ? $this->host : $host;
        if(!isset($this->list[$module][$host])) {
            $this->resolveLicense($module, $host);
        }
        return $this->list[$module][$host] !== false;
    }

    /**
     * @param   $module
     * @param   null $hash
     * @param   null $host
     * @return  bool|string
     */
    public function getLicense($module, $hash = null, $host = null)
    {
        $h = $host === null ? $this->host : $host;
        if(!isset($this->list[$module][$h])) {
            $this->resolveLicense($module, $h);
        }
        if($this->list[$module][$h] === false) {
            return false;
        }
        $license = $this->list[$module][$h]['license'];
        if($hash !== null) {
            $s = base64_decode('VZ4nYpGmROGvHdDdGsD17XxR88M=');
            $s = sha1($module . $s . $module, true);
            $license = sha1($s. $license . $hash . $host, true);
        }
        return $license;
    }

    /**
     * @param   string $module
     * @param   string|null $host
     * @return  array|bool
     */
    public function getLicenseInfo($module, $host = null)
    {
        $host = $host === null ? $this->host : $host;
        if(!isset($this->list[$module][$host])) {
            $this->resolveLicense($module, $host);
        }
        return $this->list[$module][$host];
    }

    /**
     * @param   string $license
     * @return  array|bool
     */
    public static function readLicenseInfo($license)
    {
        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);
        $info = base64_decode($license);
        if ($info === false) {
            return false;
        }
        $info = gzinflate($info);
        // License can not be unpacked.
        if ($info === false) {
            return false;
        }
        // License too long / short.
        if(strlen($info) > (512 + 60) || strlen($info) < 100) {
            return false;
        }
        $info = substr($info, 60);
        $info = unserialize($info);
        if ($info === false) {
            return false;
        }
        $info['license'] = $license;
        return $info;
    }

    /**
     * @param   string $host
     * @throws  Exception
     */
    private function setHost($host)
    {
        if(empty($host) || strlen($host) <= 3) {
            throw new Exception('No valid shop host found.');
        }
        $this->host = $host;
    }

    /**
     * @param $list
     */
    private function setRawList($list)
    {
        foreach($list as  $entry) {
            if(!empty($entry['module']) && !empty($entry['host']) && !empty($entry['license'])) {
                $this->rawList[$entry['module']][$entry['host']] = $entry['license'];
            }
        }
    }

    /**
     * @param $license
     */
    private function setCoreLicense($license)
    {
        $this->license = $license;
        $this->checkCoreLicense();
    }

    /**
     * @param   string $license
     * @param   null $host
     * @return  array|bool
     */
    private function unpackLicense($license, $host = null)
    {
        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);
        if (empty($license)) {
            return false;
        }
        $license = base64_decode($license);
        if ($license === false) {
            return false;
        }
        $license = gzinflate($license);
        // License can not be unpacked.
        if ($license === false) {
            return false;
        }
        // License too long / short.
        if(strlen($license) > (512 + 60) || strlen($license) < 100) {
            return false;
        }
        $h = substr($license, 0, 20);
        $c = substr($license, 20, 20);
        $m = substr($license, 40, 20);
        if($this->license === null) {
            $this->setCoreLicense($c);
        }
        if($this->license !== $c) {
            return false;
        }
        $license = substr($license, 60);
        if($h !== sha1($c . $license . $m, true)) {
            return false;
        }
        $license = unserialize($license);
        if ($license === false) {
            return false;
        }
        if(!empty($license['host']) && $host !== $license['host']) {
            return false;
        }
        $date = date('Ymd');
        if(!empty($license['creation']) && $license['creation'] > $date) {
            return false;
        }
        if(!empty($license['expiration']) && $license['expiration'] < $date) {
            return false;
        }
        $license['license'] = $m;
        return $license;
    }

    /**
     * @param   $module
     * @param   null $host
     * @return  bool
     */
    private function resolveLicense($module, $host = null)
    {
        $host = $host === null ? $this->host : $host;
        if(empty($this->rawList[$module][$host])) {
            $this->list[$module][$host] = false;
        } else {
            $license = $this->rawList[$module][$host];
            $this->list[$module][$host] = $this->unpackLicense($license, $host);
        }
        return $this->list[$module][$host] !== false;
    }

    public function __sleep()
    {
        return array('rawList', 'host');
    }

    public function __wakeup()
    {
        $this->license = null;;
        $this->list = array();
    }

    private function __clone()
    {

    }
}