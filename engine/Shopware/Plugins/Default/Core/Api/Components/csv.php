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
 * Zugriff auf CSV-Dateien
 *
 * @author      Heiner Lohaus <hl@shopware2.de>
 * @package     Shopware 2.08.01
 * @subpackage  API-Converter
 */
class sCsvConvert
{
    public $sSettings = array(
        "fieldmark" => "\"",
        "separator" => ";",
        "encoding"=>"ISO-8859-1",//UTF-8
        "escaped_separator" => "",
        "escaped_fieldmark" => "\"\"",
        "newline" => "\n",
        "escaped_newline" => "",
    );
    public function encode ($array,$keys = array())
    {
        if(!is_array($keys)||!count($keys))
            $keys = array_keys(current($array));
        $lastkey = end($keys);
        $csv = $this->_encode_line(array_combine($keys,$keys),$keys).$this->sSettings['newline'];
        foreach ($array as $line) {
            $csv .= $this->_encode_line($line, $keys).$this->sSettings['newline'];
        }
        return $csv;
    }
    public function encode_stream ($array,$keys = array(), &$stream = null)
    {
        if(empty($stream))
            $stream = fopen("php://output","w");
        if(!is_array($keys)||!count($keys))
            $keys = array_keys(current($array));
        $lastkey = end($keys);
        $csv = $this->_encode_line(array_combine($keys,$keys),$keys).$this->sSettings['newline'];
        foreach ($array as $line) {
            fwrite($stream,$this->_encode_line($line, $keys).$this->sSettings['newline']);
        }
        return true;
    }
    public function get_all_keys($array)
    {
        $keys = array();
        if(!empty($array)&&is_array($array))
        foreach ($array as $line)
            $keys = array_merge($keys, array_diff(array_keys($line), $keys));
        return $keys;
    }
    public function _encode_line($line, $keys)
    {
        if(isset($this->sSettings['fieldmark']))
            $fieldmark = $this->sSettings['fieldmark'];
        else
            $fieldmark = "";
        $lastkey = end($keys);
        foreach ($keys as $key) {
            if (!empty($line[$key])) {
                if (strpos($line[$key],"\r")!==false||strpos($line[$key],"\n")!==false||strpos($line[$key],$fieldmark)!==false||strpos($line[$key],$this->sSettings['separator'])!==false) {
                    $csv .= $fieldmark;
                    if($this->sSettings['encoding']=="UTF-8")
                        $line[$key] = utf8_decode($line[$key]);
                    if(!empty($fieldmark))
                        $csv .= str_replace($fieldmark,$this->sSettings['escaped_fieldmark'],$line[$key]);
                    else
                        $csv .= str_replace($this->sSettings['separator'],$this->sSettings['escaped_separator'],$line[$key]);
                    $csv .= $fieldmark;
                } else
                    $csv .= $line[$key];
            }
            if($lastkey!=$key)
                $csv .= $this->sSettings['separator'];
        }
        return $csv;
    }
    /*function decode($csv)
    {
        $separator = $this->settings['separator'];
        $lines = explode($separator, $csv);
        for ($i = 0; $i < count($lines); $i++) {
            $nquotes = substr_count($lines[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($lines); $j++) {
                    if (substr_count($lines[$j], '"') > 0) {
                        array_splice($lines, $i, $j-$i+1,
                            implode($separator, array_slice($lines, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                $qstr =& $lines[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
        }
        return $lines;
    }*/

    public function decode ($csv,$keys = array())
    {
        $csv = file_get_contents($csv);

        if(isset($this->sSettings['fieldmark']))
            $fieldmark = $this->sSettings['fieldmark'];
        else
            $fieldmark = "";
        if($this->sSettings['encoding']=="UTF-8")
            $csv = utf8_decode($csv);

        if(isset($this->sSettings['escaped_newline'])&&$this->sSettings['escaped_newline']!==false&&isset($this->sSettings['fieldmark'])&&$this->sSettings['fieldmark']!==false)
            $lines = $this->_split_line($csv);
        else
            $lines = preg_split("/\n|\r/", $csv, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($keys)||!is_array($keys)) {
            if(empty($this->sSettings['fieldmark']))
                $keys = explode($this->sSettings['separator'], $lines[0]);
            else
                $keys = $this->_decode_line($lines[0]);
            foreach ($keys as $i=>$key)
                $keys[$i] = trim($key,"? \n\t\r");
            unset($lines[0]);
        }

        foreach ($lines as $line) {
            $tmp = array();
            if(empty($this->sSettings['fieldmark']))
                $line = explode($this->sSettings['separator'], $line);
            else
                $line = $this->_decode_line($line);
            foreach ($keys as $pos=>$key)
                if(isset($line[$pos]))
                    $tmp[$key] = $line[$pos];
            $array[] = $tmp;
        }
        return $array;
    }
    public function _decode_line_old($line)
    {
        $line = $line.$this->sSettings['separator'];
        $fm = preg_quote($this->sSettings['fieldmark'],"#");
        preg_match("#[^$fm]*#A",$line,$match);
        if(!empty($match[0]))
            $values = explode($this->sSettings['separator'],substr($match[0],0,-1));
        else
            $values = array();
        $reg = "#([$fm][^$fm]*)[$fm]([^$fm]*)#";
        preg_match_all($reg,$line,$matchs);
        $tmp = "";
        foreach ($matchs[1] as $key=>$match) {
            if (!empty($matchs[1][$key])) {
                if (!empty($matchs[2][$key][0])&&$matchs[2][$key][0]==$this->sSettings['separator']) {
                    $values[] = substr($tmp.$matchs[1][$key],1);
                    $tmp = "";
                } else {
                    $tmp .= $matchs[1][$key];
                }
            }
            if (!empty($matchs[2][$key])&&strlen($matchs[2][$key])>2) {
                $values = array_merge($values, explode($this->sSettings['separator'],substr($matchs[2][$key],1,-1)));
            }
        }
        return $values;
    }
    public function _decode_line($line)
    {
        $separator = $this->sSettings['separator'];
        $fieldmark = $this->sSettings['fieldmark'];
        $elements = explode($this->sSettings['separator'], $line);
        $tmp_elements = array();
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], $this->sSettings['fieldmark']);
            if ($nquotes %2 == 1) {
                if(isset($elements[$i+1]))
                       $elements[$i+1] = $elements[$i].$this->sSettings['separator'].$elements[$i+1];
            } else {
                if ($nquotes > 0) {
                    if(substr($elements[$i],0,1)==$fieldmark)
                        $elements[$i] = substr($elements[$i],1);
                    if(substr($elements[$i],-1,1)==$fieldmark)
                        $elements[$i] = substr($elements[$i],0,-1);
                    $elements[$i] = str_replace($this->sSettings['escaped_fieldmark'], $this->sSettings['fieldmark'], $elements[$i]);
                }
                $tmp_elements[] = $elements[$i];
            }
        }
        return $tmp_elements;
    }
    public function _split_line($csv)
    {
        $lines = array();
        $elements = explode($this->sSettings['newline'], $csv);
        $tmp_line = "";
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], $this->sSettings['fieldmark']);
            if ($nquotes %2 == 1) {
                $elements[$i+1] = $elements[$i].$this->sSettings['newline'].$elements[$i+1];
            } else {
                $lines[] = $elements[$i];
            }

        }
        return $lines;
    }
}
?>
