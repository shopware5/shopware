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

namespace Shopware\Plugin\Debug\Components;

class Utils
{
    /**
     * Encode data method
     *
     * @param string|array|object $data
     * @param int                 $length
     *
     * @return array|string
     */
    public function encode($data, $length = 250)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$this->encode($key)] = $this->encode($value);
            }
        } elseif (is_string($data)) {
            if (strlen($data) > $length) {
                $data = substr($data, 0, $length - 3) . '...';
            }
        } elseif ($data instanceof \ArrayObject) {
            /** @var \ArrayObject $data */
            $data = $this->encode($data->getArrayCopy());
        } elseif ($data instanceof \Zend_Config) {
            /** @var \Zend_Config $data */
            $data = $this->encode($data->toArray());
        } elseif (method_exists($data, '__toArray') || $data instanceof \stdClass) {
            $data = $this->encode((array) $data);
        } elseif (is_object($data)) {
            $data = $data instanceof \Enlight_Hook_Proxy ? get_parent_class($data) : get_class($data);
        } else {
            $data = (string) $data;
        }

        return $data;
    }

    /**
     * Format memory in a proper way
     *
     * @param float $size
     *
     * @return string
     */
    public function formatMemory($size)
    {
        if (empty($size)) {
            return '0.00 b';
        }
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @number_format($size / (1024 ** ($i = floor(log($size, 1024)))), 2, '.', '') . ' ' . $unit[$i];
    }

    /**
     * Format time for human readable
     *
     * @param float $time
     *
     * @return string
     */
    public function formatTime($time)
    {
        return number_format($time, 5, '.', '');
    }
}
