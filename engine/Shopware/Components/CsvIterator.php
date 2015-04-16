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
 */
class Shopware_Components_CsvIterator extends Enlight_Class implements Iterator
{
    const DEFAULT_DELIMITER = ';';
    const DEFAULT_LENGTH = 60000;

    /**
     * The CSV file handler.
     *
     * @var resource
     * @access private
     */
    private $_handler = null;

    /**
     * The delimiter of the CSV file.
     *
     * @var string
     * @access private
     */
    private $_delimiter = null;

    /**
     * The delimiter of the CSV file.
     *
     * @var string
     * @access private
     */
    private $_newline = "\r\n";

    /**
     * The delimiter of the CSV file.
     *
     * @var string
     * @access private
     */
    private $_fieldmark = "\"";

    /**
     * The dafs
     *
     * @var integer
     * @access private
     */
    private $_length = 60000;

    /**
     * The row counter.
     *
     * @var integer
     * @access private
     */
    private $_key = null;

    /**
     * The element that will be returned on each iteration.
     *
     * @var mixed
     * @access private
     */
    private $_current = null;

    /**
     * The element that will be returned on each iteration.
     *
     * @var mixed
     * @access private
     */
    private $_header = null;

    /**
     * This is the constructor. It try to open the CSV file.
     *
     * @param string $filename The fullpath of the CSV file.
     * @param string $delimiter The delimiter.
     * @param integer $header
     *
     * @throws Exception
     */
    public function __construct($filename, $delimiter = self::DEFAULT_DELIMITER, $header = null)
    {
        if (($this->_handler = fopen($filename, 'r')) === false) {
            throw new Exception("The file '$filename' cannot be opened");
        }

        $this->_newline = $this->getNewLineType();

        $this->_delimiter = $delimiter;
        if (empty($header)) {
            $this->_read();
            $this->_header = $this->_current;
        } else {
            $this->_header = $header;
        }
    }

    /**
     * Helper function to determine the newline type of the file
     * @throws \Exception
     * @return string Detected new line type
     */
    private function getNewLineType()
    {
        $newLineWin = "\r\n";
        $newLineNix = "\n";

        $pos = false;
        $content = '';
        while ($pos === false && !feof($this->_handler)) {
            $content .= fread($this->_handler, 1024);
            // Get first appearance of \n
            $pos = strpos($content, "\n");
        }

        if ($pos !== false && $pos > 1) {
            rewind($this->_handler);
            // Check if the previous char is a \r. If it is we have a windows EOL
            if (substr($content, $pos-1, 1) === "\r") {
                return $newLineWin;
            } else {
                return $newLineNix;
            }
        }

        throw new Exception("New line detection failed");
    }

    public function SetFieldmark($fieldmark)
    {
        $this->_fieldmark = $fieldmark;
    }

    public function GetHeader()
    {
        return $this->_header;
    }

    /**
     * This is the destructor. It close the CSV file.
     *
     * @access public
     */
    public function __destruct()
    {
        fclose($this->_handler);
    }

    /**
     * This method move the file pointer to the next row.
     *
     * @access public
     */
    public function next()
    {
        $this->_read();
        $this->_key += 1;
    }

    /**
     * This method reset the file handler.
     *
     * @access public
     */
    public function rewind()
    {
        rewind($this->_handler);
        $this->_read();
        $this->_read();
        $this->_key = 1;
    }

    /**
     * This method returns the current row number.
     *
     * @access public
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * This methods return the current CSV row data.
     *
     * @access public
     * @return array The row as an one-dimensional array
     */
    public function current()
    {
        $data = array();
        foreach ($this->_header as $key=>$name) {
            $data[$name] = isset($this->_current[$key]) ? $this->_current[$key] : '';
        }
        return $data;
    }

    /**
     * This method checks if the current row is readable.
     *
     * @access public
     * @return boolean If the current row is readable.
     */
    public function valid()
    {
        return $this->_current !== false;
    }

    /**
     * This method read the next row of the CSV file.
     *
     * @access private
     */
    private function _read()
    {
        //$this->_current = fgetcsv($this->_handler, $this->_length, $this->_delimiter);
        if (!$this->_handler||feof($this->_handler)) {
            $this->_current = false;
            return;
        }
        $count = 0;
        $line = stream_get_line($this->_handler, $this->_length, $this->_newline);

        // remove possible utf8-bom
        if (substr($line, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $line = substr($line, 3);
        }

        while ((empty($this->_fieldmark)||($count = substr_count($line, $this->_fieldmark)) % 2 != 0)&&!feof($this->_handler)) {
            $line .= $this->_newline.stream_get_line($this->_handler, $this->_length, $this->_newline);
        }
        if (empty($line)) {
            $this->_current = false;
            return;
        }
        $line = explode($this->_delimiter, $line);
        if (empty($count)) {
            $this->_current = $line;
            return;
        }
        $this->_current = array();
        $row = "";
        do {
            $row .= current($line);
            $count = substr_count($row, $this->_fieldmark);
            if ($count % 2 != 0) {
                $row .= ";";
                continue;
            } elseif ($count) {
                $this->_current[] = str_replace($this->_fieldmark.$this->_fieldmark, $this->_fieldmark, substr($row, 1, -1));
            } else {
                $this->_current[] = $row;
            }
            $row = "";
        } while (next($line)!==false);
    }
}
