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

class Shopware_Components_Convert_Excel
{
    private $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

    private $footer = "</Workbook>";

    private $lines = array();

    private $worksheet_title = "Table1";

    public function encodeRow($array)
    {
        $cells = '';

        // foreach key -> write value into cells
        foreach ($array as $value) {
            $type = 'String';
            if (preg_match("/^[0-9]{1,11}$/", $value)) {
                $type = 'Number';
            }

            $value = str_replace('&#039;', '&apos;', htmlspecialchars($value, ENT_QUOTES));
            $value = str_replace(array("\r\n", "\r", "\n"), '&#10;', $value);
            $cells .= sprintf("<Cell><Data ss:Type=\"%s\">%s</Data></Cell>\n", $type, $value);
        }

        // transform $cells content into one row
        return "<Row>\n" . $cells . "</Row>\n";
    }

    public function addRow($array)
    {
        $this->lines[] = $this->encodeRow($array);
    }

    public function addArray($array)
    {
        foreach ($array as $row):
            $this->addRow($row);
        endforeach;
    }

    public function setTitle($title)
    {
        // strip out special chars first
        $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);

        // now cut it to the allowed length
        $title = substr($title, 0, 31);

        // set title
        $this->worksheet_title = $title;
    }

    public function generateXML($filename)
    {
        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo $this->getHeader();
        echo implode("\n", $this->lines);
        echo $this->getFooter();
    }

    public function getAll()
    {
        $r = $this->getHeader();
        $r .= implode('', $this->lines);
        $r .= $this->getFooter();
        return $r;
    }

    public function getHeader()
    {
        $header = stripslashes($this->header);
        $header .= "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
        $header .= "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
        return $header;
    }

    public function getFooter()
    {
        $footer = "</Table>\n</Worksheet>\n";
        $footer .= $this->footer;
        return $footer;
    }
}
