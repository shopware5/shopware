<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Shopware API
 * Mapping von Shopware Datenfeldern
 *
 * @author      Heiner Lohaus <hl@shopware2.de>
 * @package     Shopware 2.08.01
 * @subpackage  API-Converter
 */
class sMappingConvert
{
    private $sFunctions;
    public $sAPI;

    public function __construct()
    {
        //$value,$vars,$config,&$array
        $this->sFunctions = array(
            "intval" => create_function('$value','return intval($value);'),
            "strval" => "strval",
            "current" => create_function('$array','if(empty($array)||!is_array($array)) return false; return current($array);'),
            "get_value" => create_function('$array,$key','if(!isset($array[$key])) return false; return $array[$key];'),
            "load_values" => create_function('$value,$vars,$config,&$array','if(empty($array)||!is_array($array)) return false; $array = array_merge($value,$array); return true;'),
            "put_value" => create_function('$value,$key','if(empty($value)) return false; return array($key=>$value);'),
            "put_key" => create_function('$array,$name','if(empty($array)||!is_array($array)||empty($name)||!is_string($name)) return false; foreach($array as $key=>$value) $array[$key][$name]=$key; return $array;'),
            "change_key" => create_function ('$array,$key','if(empty($array)||!is_array($array)) return false; $tmp = array(); foreach($array as $value) $tmp[$value[$key]] = $value; return $tmp;'),
            "key_as_atr" => create_function ('$array,$name','if(empty($array)||!is_array($array)||empty($name)||!is_string($name)) return false; $tmp = array(); foreach($array as $key=>$value) $tmp[] = array("_attributes"=>array($name=>$key),"_value"=>$value);  return $tmp;'),
            //"move_value" => create_function('$value,$key','if(empty($key)) return false; return array($key=>$value);'),
            "implode" => create_function('$value,$config','return implode($config["separator"], $value);'),
            //"rename_array" => create_function('$array,$config','if(is_array($array)&&count($array)) foreach($array as $key=>$value) $ret[$key] = $value; return $ret;'),
            "explode" => create_function('$value,$separator','return explode($separator, $value);'),
            "strip_tags" => create_function('$value','return strip_tags($value);'),
            "trim" => create_function('$value','return trim($value);'),
            "strip_whitespaces" => create_function('$value','return preg_replace("/\s\s+/", " ", $value);'),
            "html_decode" => create_function('$value','return html_entity_decode($value, ENT_QUOTES);'),
            //"cleanup_url" => create_function('$value,$vars,$config,&$sAPI','return $sAPI->sSystem->cleanup_url($value);'),
            "substr" => create_function('$value,$config','if(empty($config["length"])) $config["length"] = 0; return substr($value, $config["start"], $config["length"]);'),
            "boolval" => create_function('$value','switch (strtolower(strval($value))) { case ("false"): case ("null"): case ("0"): case ("no"): case ("nein"): case ("\0"): case (""): case (null): return false; default: return true; }'),
            "map" => array(&$this,"_func_mapping"),
            "mask_row" => array(&$this,"_func_convert_row")
        );
    }
    public function prepare_mask($mask)
    {
        if(empty($mask)||!is_array($mask))
            return false;
        $mask_tmp = array();
        foreach ($mask as $name=>$config) {
            if(empty($config))
                continue;
            if(is_string($config))
                $config = array('field'=>$config);
            if (!isset($config['field']))
                $config['field'] = $name;
            elseif(is_int($name))
                $name = $config['field'];
            if(!is_array($config))
                continue;
            if(!empty($config['convert'])&&is_string($config['convert']))
                $config['convert'] = array($config['convert']=>"");
            foreach ($config as $key=>$con) {
                if (is_int($key)) {
                    $config[$con] = false;
                    unset($config[$key]);
                }
            }
            $convert_tmp = array();
            if(!empty($config['convert'])&&is_array($config['convert']))
            foreach ($config['convert'] as $func=>$vars) {
                if (is_int($func)) {
                    $func = $vars;
                    $vars = "";
                }
                $convert_tmp[$func] = $vars;
            }
            $mask_tmp[$name] = $config;
            if(!empty($convert_tmp))
                $mask_tmp[$name]['convert'] = $convert_tmp;
        }
        return $mask_tmp;
    }
    public function convert_array($mask, $array, $prepare=false)
    {
        if(empty($array)||!is_array($array))
            return false;
        if(empty($mask)||!is_array($mask))
            return false;
        if(!$prepare)
            $mask = $this->prepare_mask ($mask);
        foreach ($array as $key=>$line) {
            $values = array();
            foreach ($mask as $name=>$config) {
                $name = rtrim($name,"_");
                if(isset($line[$config['field']]))
                    $value = $line[$config['field']];
                elseif(isset($config["isset"]))
                    continue;
                else
                    $value = "";
                if(empty($value)&&isset($config["empty"]))
                    continue;
                if(isset($config['convert']))
                foreach ($config['convert'] as $func=>$vars) {
                    $func = rtrim($func,"_");
                    if(isset($this->sFunctions[$func]))
                        $value = call_user_func_array($this->sFunctions[$func],array($value,$vars,$config,&$line));
                    //$value = call_user_func($this->sFunctions[$func],$value,$vars,$config,&$line);
                }
                if(isset($config["put_back"]))
                    $line[$name] = $value;
                $values[$name] = $value;
            }
            $data[$key] = $values;
        }
        return $data;
    }

    public function convert_line($mask, $line, $prepare=false)
    {
        if(empty($line)||!is_array($line))
            return false;
        if(empty($mask)||!is_array($mask))
            return false;
        if(!$prepare)
            $mask = $this->prepare_mask ($mask);
        $values = array();
        foreach ($mask as $name=>$config) {
            $name = rtrim($name,"_");
            if(isset($line[$config['field']]))
                $value = $line[$config['field']];
            elseif(isset($config["isset"]))
                continue;
            else
                $value = "";
            if(empty($value)&&isset($config["empty"]))
                continue;
            if(isset($config['convert']))
            foreach ($config['convert'] as $func=>$vars) {
                $func = rtrim($func,"_");
                if(isset($this->sFunctions[$func]))
                    $value = call_user_func_array($this->sFunctions[$func],array($value,$vars,$config,&$line));
                //$value = call_user_func($this->sFunctions[$func],$value,$vars,$config,&$line);
            }
            if(in_array("put_back",$config))
                $line[$name] = $value;
            $values[$name] = $value;
        }
        return $values;
    }

    public function convert_row($config, $array)
    {
        if(empty($config))
            return false;
        if(empty($config['convert']))
            $config['convert'] = $config;
        if(is_string($config['convert']))
            $config = array('convert'=>array($config=>""));
        if(!is_array($config))
            return false;
        if(!empty($config['convert'])&&is_string($config['convert']))
            $config['convert'] = array($config['convert']=>"");
        $data = array();
        foreach ($array as $key=>$value) {
            if(!empty($config['convert'])&&is_array($config['convert'])&&count($config['convert']))
            foreach ($config['convert'] as $func=>$vars) {
                if (is_int($func)) {
                    $func = $vars;
                    $vars = "";
                }
                $func = rtrim($func,"_");
                if(isset($this->sFunctions[$func]))
                    $value = call_user_func_array($this->sFunctions[$func],array($value,$vars,$config,&$array));
                //$value = call_user_func($this->sFunctions[$func],$value,$vars,$config,array(&$array));
            }
            $data[$key] = $value;
        }
        return $data;
    }

    public function register_function($name, $func = "")
    {
        if(empty($name))
            return false;
        if(empty($func))
            $func = $name;
        if(!is_callable($func))
            return false;
        $name = rtrim($name,"_");
        $this->sFunctions[$name] = $func;
    }

    public function unregister_function($func)
    {
        if(isset($this->sFunctions[$func]))
            unset($this->sFunctions[$func]);
        return true;
    }

    public function _func_mapping($value,$vars)
    {
        if (empty($vars)||!is_array($vars)||!count($vars))
            return false;
        if (isset($vars[$value]))
            return $vars[$value];
        if(isset($vars['_default']))
            return $vars['_default'];
        return false;
    }
    public function _func_convert_row($value,$vars)
    {
        return $this->convert_row($vars, $value);
    }
}
?>
