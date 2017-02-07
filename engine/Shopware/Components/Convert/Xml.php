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
 * Shopware API
 * Zugriff auf XML-Dateien
 *
 * @deprecated since 5.3, to be removed in 5.4
 *
 * @author      Heiner Lohaus <hl@shopware2.de>
 */
class Shopware_Components_Convert_Xml
{
    public $sSettings = [
        'encoding' => 'UTF-8',
        'standalone' => true,
        'attributes' => true,
        'root_element' => '',
        'padding' => "\t",
        'newline' => "\r\n",
    ];

    public function encode($array)
    {
        $standalone = $this->sSettings['standalone'] ? 'yes' : 'no';
        $ret =
                "<?xml version=\"1.0\" encoding=\"{$this->sSettings['encoding']}\" standalone=\"$standalone\"?>{$this->sSettings['newline']}";
        $ret .= $this->_encode($array);

        return $ret;
    }

    public function _encode($array, $pos = 0, $ekey = '')
    {
        $ret = '';
        if ($this->sSettings['padding'] !== false) {
            $pad = str_repeat($this->sSettings['padding'], $pos);
        } else {
            $pad = '';
        }
        foreach ($array as $key => $item) {
            if (!empty($ekey)) {
                $key = $ekey;
            }
            $attributes = '';
            if (is_array($item) && isset($item['_attributes'])) {
                foreach ($item['_attributes'] as $k => $v) {
                    $attributes .= " $k=\"" . htmlspecialchars($v) . '"';
                }
                if (isset($item['_value'])) {
                    $item = $item['_value'];
                } else {
                    unset($item['_attributes'], $item['_value']);
                }
            }
            if (empty($item)) {
                $ret .= "$pad<$key$attributes></$key>{$this->sSettings['newline']}";
            } elseif (is_array($item)) {
                if (is_numeric(key($item))) {
                    $ret .= $this->_encode($item, $pos, $key);
                } else {
                    $ret .= "$pad<$key$attributes>{$this->sSettings['newline']}" . $this->_encode(
                        $item, $pos + 1
                    ) . "$pad</$key>{$this->sSettings['newline']}";
                }
            } else {
                if (preg_match('#<|>|&(?<!amp;)#', $item)) {
                    //$item = str_replace("<![CDATA[", "&lt;![CDATA[", $item);
                    $item = str_replace(']]>', ']]]]><![CDATA[>', $item);
                    $ret .= "$pad<$key$attributes><![CDATA[" . $item . "]]></$key>{$this->sSettings['newline']}";
                } else {
                    $ret .= "$pad<$key$attributes>" . $item . "</$key>{$this->sSettings['newline']}";
                }
            }
        }

        return $ret;
    }

    public function decode($contents)
    {
        if (!$contents) {
            return [];
        }
        if (!function_exists('xml_parser_create')) {
            return [];
        }
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $this->sSettings['encoding']);
        xml_parse_into_struct($parser, file_get_contents($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values) {
            return;
        }

        $xml_array = [];
        $current = &$xml_array;

        foreach ($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble
            extract($data); //We could use the array by itself, but this cooler.
            $result = '';
            if (!empty($attributes)) { //The second argument of the function decides this.
                $result = [];
                if (isset($value)) {
                    $result['_value'] = $value;
                }

                //Set the attributes too.
                if (isset($attributes)) {
                    foreach ($attributes as $attr => $val) {
                        if ($this->sSettings['attributes']) {
                            $result['_attributes'][$attr] = $val;
                        } //Set all the attributes in a array called 'attr'
                        /*  TO DO should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
                    }
                }
            } elseif (isset($value)) {
                $result = $value;
            }

            //See tag status and do the needed.
            if ($type == 'open') { //The starting of the tag '<tag>'
                $parent[$level - 1] = &$current;

                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    $current = &$current[$tag];
                } else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) {
                        array_push($current[$tag], $result);
                    } else {
                        $current[$tag] = [$current[$tag], $result];
                    }
                    $last = count($current[$tag]) - 1;
                    $current = &$current[$tag][$last];
                }
            } elseif ($type == 'complete') { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                } else { //If taken, put all things inside a list(array)
                    if ((is_array(
                        $current[$tag]
                    ) and $this->sSettings['attributes'] == 0) //If it is already an array...
                            or (isset($current[$tag][0]) and is_array(
                                $current[$tag]
                            ) and $this->sSettings['attributes'] == 1)
                    ) {
                        //array_push($current[$tag],$result); // ...push the new element into that array.
                        $current[$tag][] = $result;
                    } else { //If it is not an array...
                        $current[$tag] = [
                            $current[$tag], $result,
                        ]; //...Make it an array using using the existing value and the new value
                    }
                }
            } elseif ($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level - 1];
            }
        }

        return $xml_array;
    }

    public function fix_array(&$array, $name = '')
    {
        if (!empty($name) && (empty($array[$name]) || !is_array($array[$name]))) {
            return false;
        }
        if (!empty($name)) {
            $array = $array[$name];
        }
        if (empty($array) || !is_array($array)) {
            return false;
        }
        if (key($array) !== 0) {
            $array = [0 => $array];
        }

        return true;
    }

    public function fix_string(&$string)
    {
        if (empty($string)) {
            return false;
        }
        if (!is_array($string)) {
            $string = ['_value' => $string];
        }
        if (empty($string['_value'])) {
            $string['_value'] = '';
        }

        return true;
    }

    public function attr_as_key(&$array, $atr, $valuename = '')
    {
        $data = [];
        if (!empty($array) && is_array($array)) {
            foreach ($array as $value) {
                if (!isset($value['_attributes'][$atr])) {
                } elseif (isset($value['_value'])) {
                    $data[$value['_attributes'][$atr]] = $value['_value'];
                } elseif (!empty($valuename)) {
                    if (isset($value[$valuename])) {
                        $data[$value['_attributes'][$atr]] = $value[$valuename];
                    } else {
                        $data[$value['_attributes'][$atr]] = null;
                    }
                } else {
                    $data[$value['_attributes'][$atr]] = $value;
                    unset($data[$value['_attributes'][$atr]]['_attributes'][$atr]);
                    if (empty($data[$value['_attributes'][$atr]]['_attributes'])) {
                        unset($data[$value['_attributes'][$atr]]['_attributes']);
                    }
                    if (empty($data[$value['_attributes'][$atr]])) {
                        $data[$value['_attributes'][$atr]] = null;
                    }
                }
            }
        }
        $array = $data;
    }

    public function value_as_key(&$array, $name, $valuename = '')
    {
        $data = [];
        if (!empty($array) && is_array($array)) {
            foreach ($array as $value) {
                if (!isset($value[$name])) {
                    $data[$value[$name]] = null;
                } elseif (!empty($valuename)) {
                    if (isset($value[$valuename])) {
                        $data[$value[$name]] = $value[$valuename];
                    } else {
                        $data[$value[$name]] = null;
                    }
                } else {
                    $data[$value[$name]] = $value;
                }
            }
        }
        $array = $data;
    }

    public function atr_as_values(&$array, $valuename = '')
    {
        if (!empty($valuename) && is_string($array)) {
            $array[$valuename] = $array;
        }
        if (empty($array) || !is_array($array)) {
            return false;
        }
        if (!empty($array['_attributes']) && is_array(
            $array['_attributes']
        )
        ) {
            foreach ($array['_attributes'] as $key => $value) {
                $array[$key] = $value;
            }
        }
        unset($array['_attributes']);
        if (!empty($valuename) && isset($array['_value'])) {
            $array[$valuename] = $array['_value'];
            unset($array['_value']);
        }
    }
}
